@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-300 focus:border-[#F27405] focus:ring-4 focus:ring-[#F27405]/15 focus:outline-none outline-none rounded-xl text-sm transition-all']) }}>
