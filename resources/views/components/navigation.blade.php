{{-- Navigation Menu Component --}}
<nav class="sticky top-0 z-[200] h-16 bg-[rgba(8,8,10,0.9)] border-b border-white/[0.07] backdrop-blur-xl" role="navigation" aria-label="Main navigation">
  <div class="max-w-[1120px] mx-auto px-12 h-full flex items-center justify-between max-md:px-6 max-sm:px-4">
    <!-- Logo -->
    <a href="{{ url('/') }}" class="flex items-center gap-[11px] no-underline" aria-label="Toddy'z Restaurant home">
      <div class="w-9 h-9 rounded-full bg-[#D85A30] flex items-center justify-center shrink-0">
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <circle cx="9" cy="9" r="7.5" stroke="white" stroke-width="1.4"/>
          <path d="M5.5 9 Q9 5 12.5 9 Q9 13 5.5 9Z" fill="white" opacity=".9"/>
        </svg>
      </div>
      <div>
        <div class="font-display text-[18px] text-[#F8F8F6] leading-none">Toddy'<span class="text-[#D85A30]">z</span></div>
        <div class="text-[9px] text-[rgba(248,248,246,0.32)] uppercase tracking-[.06em] mt-0.5">Family Restaurant</div>
      </div>
    </a>
    
    <!-- Desktop links -->
    <div class="flex items-center gap-7 max-[680px]:hidden">
      <a href="{{ url('/menu/public') }}" class="font-ui text-[12px] font-medium text-[rgba(248,248,246,0.58)] no-underline hover:text-[#F8F8F6] transition-colors {{ request()->is('menu*') ? 'text-[#F8F8F6]' : '' }}">Menu</a>
      <a href="{{ url('/specials') }}" class="font-ui text-[12px] font-medium text-[rgba(248,248,246,0.58)] no-underline hover:text-[#F8F8F6] transition-colors {{ request()->is('specials*') ? 'text-[#F8F8F6]' : '' }}">Specials</a>
      <a href="{{ url('/productions') }}" class="font-ui text-[12px] font-medium text-[rgba(248,248,246,0.58)] no-underline hover:text-[#F8F8F6] transition-colors {{ request()->is('productions*') ? 'text-[#F8F8F6]' : '' }}">Productions</a>
      <a href="{{ url('/contact') }}" class="font-ui text-[12px] font-medium text-[rgba(248,248,246,0.58)] no-underline hover:text-[#F8F8F6] transition-colors {{ request()->is('contact*') ? 'text-[#F8F8F6]' : '' }}">Contact</a>
      <a href="{{ url('/menu?table=1') }}" class="bg-[#D85A30] text-[#0d0d0d] font-ui text-[12px] font-semibold tracking-[.03em] px-5 py-[9px] rounded-full no-underline hover:bg-[#bf4e26] hover:-translate-y-px transition-all whitespace-nowrap">Order Now</a>
      <a href="{{ url('/admin/login') }}" class="font-ui text-[12px] font-medium text-[rgba(248,248,246,0.58)] no-underline hover:text-[#F8F8F6] transition-colors {{ request()->is('admin*') ? 'text-[#F8F8F6]' : '' }}" aria-label="Admin Login">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="vertical-align: -2px;">
          <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
        </svg>
      </a>
    </div>
    
    <!-- Hamburger -->
    <button id="tzHam" class="hidden p-1.5 bg-transparent border-none cursor-pointer flex flex-col gap-[5px] max-[680px]:flex" aria-label="Open menu" aria-expanded="false">
      <span class="block w-[22px] h-[2px] bg-[#F8F8F6] rounded-[1px] transition-all duration-[280ms]"></span>
      <span class="block w-[22px] h-[2px] bg-[#F8F8F6] rounded-[1px] transition-all duration-[280ms]"></span>
      <span class="block w-[22px] h-[2px] bg-[#F8F8F6] rounded-[1px] transition-all duration-[280ms]"></span>
    </button>
  </div>
</nav>

<!-- Drawer mask -->
<div id="tzMask" class="fixed inset-0 bg-black/55 z-[300] opacity-0 invisible transition-all duration-[280ms]"></div>
<!-- Drawer -->
<div id="tzDrawer" class="fixed top-0 right-[-100%] w-[min(88vw,360px)] h-full bg-[#1a1c24] border-l border-white/[.07] z-[400] transition-[right] duration-300 ease-[cubic-bezier(.4,0,.2,1)] px-6 pt-20 pb-10 overflow-y-auto flex flex-col gap-2">
  <a href="{{ url('/') }}" class="block px-[18px] py-[15px] bg-[#0a0c10] border border-white/[.07] rounded-[14px] text-[#F8F8F6] font-ui text-[15px] font-medium no-underline hover:border-[#D85A30]/30 hover:bg-[#D85A30]/10 hover:translate-x-1 transition-all">Home</a>
  <a href="{{ url('/menu/public') }}" class="block px-[18px] py-[15px] bg-[#0a0c10] border border-white/[.07] rounded-[14px] text-[#F8F8F6] font-ui text-[15px] font-medium no-underline hover:border-[#D85A30]/30 hover:bg-[#D85A30]/10 hover:translate-x-1 transition-all">Menu</a>
  <a href="{{ url('/specials') }}" class="block px-[18px] py-[15px] bg-[#0a0c10] border border-white/[.07] rounded-[14px] text-[#F8F8F6] font-ui text-[15px] font-medium no-underline hover:border-[#D85A30]/30 hover:bg-[#D85A30]/10 hover:translate-x-1 transition-all">Specials</a>
  <a href="{{ url('/productions') }}" class="block px-[18px] py-[15px] bg-[#0a0c10] border border-white/[.07] rounded-[14px] text-[#F8F8F6] font-ui text-[15px] font-medium no-underline hover:border-[#D85A30]/30 hover:bg-[#D85A30]/10 hover:translate-x-1 transition-all">Productions</a>
  <a href="{{ url('/contact') }}" class="block px-[18px] py-[15px] bg-[#0a0c10] border border-white/[.07] rounded-[14px] text-[#F8F8F6] font-ui text-[15px] font-medium no-underline hover:border-[#D85A30]/30 hover:bg-[#D85A30]/10 hover:translate-x-1 transition-all">Contact</a>
  <a href="{{ url('/menu?table=1') }}" class="mt-4 block text-center px-[18px] py-[15px] bg-[#D85A30] text-white font-ui text-[15px] font-semibold no-underline rounded-[14px] hover:bg-[#bf4e26] transition-colors">Order Now</a>
</div>
