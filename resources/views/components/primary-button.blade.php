<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-[#F27405] to-[#f59e0b] hover:from-[#d96504] hover:to-[#d97706] active:scale-[0.99] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-wider shadow-sm hover:shadow transition-all duration-150 focus:outline-none focus:ring-4 focus:ring-[#F27405]/20 cursor-pointer']) }}>
    {{ $slot }}
</button>
