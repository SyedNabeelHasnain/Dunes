<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Models\SubscriberGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSubscriberController extends Controller
{
    /**
     * Display subscribers listing with filters.
     */
    public function index(Request $request)
    {
        $query = Subscriber::with('groups')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('group_id')) {
            $query->byGroup($request->group_id);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('email', 'like', "%{$s}%")
                    ->orWhere('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $subscribers = $query->paginate(25)->withQueryString();
        $groups = SubscriberGroup::withCount('subscribers')->get();

        $stats = [
            'total' => Subscriber::count(),
            'subscribed' => Subscriber::where('status', 'subscribed')->count(),
            'unsubscribed' => Subscriber::where('status', 'unsubscribed')->count(),
            'bounced' => Subscriber::where('status', 'bounced')->count(),
        ];

        return view('admin.subscribers.index', compact('subscribers', 'groups', 'stats'));
    }

    /**
     * Store a new subscriber manually.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email:filter|unique:subscribers,email|max:255',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:subscribed,unsubscribed,bounced',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:subscriber_groups,id',
        ]);

        $subscriber = Subscriber::create([
            'email' => strtolower(trim($validated['email'])),
            'first_name' => $validated['first_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
            'source' => 'admin_import',
            'subscribed_at' => $validated['status'] === 'subscribed' ? now() : null,
        ]);

        if (! empty($validated['groups'])) {
            $subscriber->groups()->sync($validated['groups']);
        }

        return redirect()->route('admin.subscribers.index')
            ->with('success', "Subscriber {$subscriber->email} added successfully.");
    }

    /**
     * Update an existing subscriber.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $subscriber = Subscriber::findOrFail($id);

        $validated = $request->validate([
            'email' => "required|email:filter|unique:subscribers,email,{$id}|max:255",
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:subscribed,unsubscribed,bounced',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:subscriber_groups,id',
        ]);

        $statusChanged = $subscriber->status !== $validated['status'];

        $subscriber->update([
            'email' => strtolower(trim($validated['email'])),
            'first_name' => $validated['first_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
            'subscribed_at' => $statusChanged && $validated['status'] === 'subscribed' ? now() : $subscriber->subscribed_at,
            'unsubscribed_at' => $statusChanged && $validated['status'] === 'unsubscribed' ? now() : $subscriber->unsubscribed_at,
        ]);

        if (isset($validated['groups'])) {
            $subscriber->groups()->sync($validated['groups']);
        }

        return redirect()->route('admin.subscribers.index')
            ->with('success', "Subscriber {$subscriber->email} updated successfully.");
    }

    /**
     * Fast toggle status between subscribed and unsubscribed.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $subscriber = Subscriber::findOrFail($id);
        $newStatus = $subscriber->status === 'subscribed' ? 'unsubscribed' : 'subscribed';

        $subscriber->update([
            'status' => $newStatus,
            'subscribed_at' => $newStatus === 'subscribed' ? now() : $subscriber->subscribed_at,
            'unsubscribed_at' => $newStatus === 'unsubscribed' ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'message' => "Subscriber marked as {$newStatus}.",
        ]);
    }

    /**
     * Soft delete subscriber.
     */
    public function destroy(int $id): RedirectResponse
    {
        $subscriber = Subscriber::findOrFail($id);
        $email = $subscriber->email;
        $subscriber->delete();

        return redirect()->route('admin.subscribers.index')
            ->with('success', "Subscriber {$email} deleted successfully.");
    }

    /**
     * Export Subscribers to CSV with Formula Injection Sanitization.
     */
    public function exportCsv(Request $request)
    {
        $fileName = 'dunes-subscribers-'.date('Y-m-d-His').'.csv';
        $query = Subscriber::with('groups')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('group_id')) {
            $query->byGroup($request->group_id);
        }

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['ID', 'Email', 'First Name', 'Last Name', 'Phone', 'Status', 'Source', 'Groups', 'Country', 'City', 'Subscribed At', 'Unsubscribed At', 'Reason'];

        $sanitize = function (array $row): array {
            return array_map(function ($val) {
                if ($val === null) {
                    return '';
                }
                $str = (string) $val;
                if (isset($str[0]) && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
                    return "'".$str;
                }

                return $str;
            }, $row);
        };

        $callback = function () use ($query, $columns, $sanitize) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            $query->chunk(100, function ($rows) use ($file, $sanitize) {
                foreach ($rows as $s) {
                    $groupNames = $s->groups->pluck('name')->implode(', ');
                    fputcsv($file, $sanitize([
                        $s->id,
                        $s->email,
                        $s->first_name ?: '',
                        $s->last_name ?: '',
                        $s->phone ?: '',
                        $s->status,
                        $s->source,
                        $groupNames,
                        $s->country ?: '',
                        $s->city ?: '',
                        $s->subscribed_at ? $s->subscribed_at->format('Y-m-d H:i') : '',
                        $s->unsubscribed_at ? $s->unsubscribed_at->format('Y-m-d H:i') : '',
                        $s->unsubscribe_reason ?: '',
                    ]));
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import Subscribers from CSV File.
     */
    public function importCsv(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
            'group_id' => 'nullable|exists:subscriber_groups,id',
        ]);

        $file = $request->file('csv_file');
        $groupId = $request->input('group_id');
        $handle = fopen($file->getPathname(), 'r');

        if (! $handle) {
            return back()->with('error', 'Could not open CSV file.');
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);

            return back()->with('error', 'Empty CSV file.');
        }

        // Clean BOM and lowercase header keys
        $header = array_map(function ($col) {
            return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $col)));
        }, $header);

        $emailIndex = array_search('email', $header);
        if ($emailIndex === false) {
            fclose($handle);

            return back()->with('error', 'CSV must contain an "email" header column.');
        }

        $firstNameIndex = array_search('first_name', $header);
        if ($firstNameIndex === false) {
            $firstNameIndex = array_search('firstname', $header);
        }
        if ($firstNameIndex === false) {
            $firstNameIndex = array_search('name', $header);
        }

        $lastNameIndex = array_search('last_name', $header);
        if ($lastNameIndex === false) {
            $lastNameIndex = array_search('lastname', $header);
        }

        $phoneIndex = array_search('phone', $header);

        $imported = 0;
        $updated = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (! isset($row[$emailIndex])) {
                    continue;
                }
                $email = strtolower(trim($row[$emailIndex]));
                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }

                $firstName = ($firstNameIndex !== false && isset($row[$firstNameIndex])) ? trim($row[$firstNameIndex]) : null;
                $lastName = ($lastNameIndex !== false && isset($row[$lastNameIndex])) ? trim($row[$lastNameIndex]) : null;
                $phone = ($phoneIndex !== false && isset($row[$phoneIndex])) ? trim($row[$phoneIndex]) : null;

                $sub = Subscriber::where('email', $email)->first();
                if ($sub) {
                    $sub->update([
                        'first_name' => $firstName ?: $sub->first_name,
                        'last_name' => $lastName ?: $sub->last_name,
                        'phone' => $phone ?: $sub->phone,
                    ]);
                    $updated++;
                } else {
                    $sub = Subscriber::create([
                        'email' => $email,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'phone' => $phone,
                        'status' => 'subscribed',
                        'source' => 'admin_import',
                        'subscribed_at' => now(),
                    ]);
                    $imported++;
                }

                if ($groupId && ! $sub->groups()->where('group_id', $groupId)->exists()) {
                    $sub->groups()->attach($groupId);
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);

            return back()->with('error', 'Import error: '.$e->getMessage());
        }

        fclose($handle);

        return redirect()->route('admin.subscribers.index')
            ->with('success', "Import completed: {$imported} new subscribers added, {$updated} existing records updated.");
    }

    /**
     * Bulk Action on Selected Subscribers.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:unsubscribe,subscribe,assign_group,delete',
            'subscriber_ids' => 'required|array',
            'subscriber_ids.*' => 'exists:subscribers,id',
            'target_group_id' => 'nullable|exists:subscriber_groups,id',
        ]);

        $ids = $validated['subscriber_ids'];
        $action = $validated['action'];
        $count = count($ids);

        if ($action === 'unsubscribe') {
            Subscriber::whereIn('id', $ids)->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
                'unsubscribe_reason' => 'Admin bulk action',
            ]);
            $msg = "{$count} subscribers unsubscribed.";
        } elseif ($action === 'subscribe') {
            Subscriber::whereIn('id', $ids)->update([
                'status' => 'subscribed',
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]);
            $msg = "{$count} subscribers marked active.";
        } elseif ($action === 'assign_group' && ! empty($validated['target_group_id'])) {
            $group = SubscriberGroup::findOrFail($validated['target_group_id']);
            foreach ($ids as $subId) {
                DB::table('subscriber_group_pivot')->updateOrInsert(
                    ['subscriber_id' => $subId, 'group_id' => $group->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
            $msg = "{$count} subscribers assigned to '{$group->name}'.";
        } elseif ($action === 'delete') {
            Subscriber::whereIn('id', $ids)->delete();
            $msg = "{$count} subscribers deleted.";
        } else {
            return back()->with('error', 'Invalid action.');
        }

        return redirect()->route('admin.subscribers.index')->with('success', $msg);
    }

    /**
     * Redirect create request to index modal.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('admin.subscribers.index');
    }

    /**
     * Redirect show request to index filter.
     */
    public function show(int $id): RedirectResponse
    {
        return redirect()->route('admin.subscribers.index');
    }

    /**
     * Redirect edit request to index modal.
     */
    public function edit(int $id): RedirectResponse
    {
        return redirect()->route('admin.subscribers.index');
    }
}
