<!-- Backdrop Layer -->
<div id="backdrop" class="hidden fixed inset-0 bg-black bg-opacity-40 z-[60]"></div>

<!-- Sidebar -->
<aside class="inset-0 md:left-0 -left-[300px] bg-white h-full p-8 fixed overflow-y-auto max-w-[300px] w-full z-[65] ease-out duration-300" id="sidebar">
  <div class="w-full pb-20 flex flex-col gap-3 mt-5">
    <!-- navigation menu -->
    <ul class="space-y-3">
        <li>
            <a href="{{ route("transaction_detail.dashboard") }}" class="flex items-center gap-2 text-base font-medium hover:bg-yellow-400 p-1 pr-2 rounded-lg {{ request()->routeIs('transaction_detail.*') ? "bg-yellow-400" : "" }}">
                <div class="w-8 h-8 rounded-md bg-blue-950 flex justify-center items-center">
                <i class="fa-solid fa-chart-simple text-yellow-400"></i>
                </div>
                {{ __("Transaction Detail") }}
            </a>
        </li>

        <li>
            <a href="{{ route("recap_at4.dashboard") }}" class="flex items-center gap-2 text-base font-medium hover:bg-yellow-400 p-1 pr-2 rounded-lg {{ request()->routeIs('recap_at4.*') ? "bg-yellow-400" : "" }}">
                <div class="w-8 h-8 rounded-md bg-blue-950 flex justify-center items-center">
                <i class="fa-regular fa-paste text-yellow-400"></i>
                </div>
                {{ __("AT4 Recap") }}
            </a>
        </li>

        <li>
            <details {{ request()->routeIs('data_compare.transaction_detail.*') ? "open" : ""}}>
                <summary class="hover:bg-yellow-400 p-1 pr-2 flex items-center justify-between text-base font-medium rounded-lg {{ request()->routeIs('data_compare.transaction_detail.*') ? "bg-yellow-400" : "" }}">
                    <a href="#" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-md bg-blue-950 flex justify-center items-center">
                        <i class="fa-solid fa-code-compare text-yellow-400"></i>
                        </div>
                        {{ __("Data Compare") }}
                    </a>
    
                    <i id="caret-menu" class="fa-solid fa-angle-up transition-all duration-100"></i>
                </summary>

                <ul id="submenu" class="ml-5 border-l-[1.5px] border-slate-200 mt-3 space-y-2">
                    <li>  
                        <a href="{{ route("data_compare.transaction_detail.dashboard") }}" class="text-base font-normal pl-5 border-l-[1.5px] -ml-[1.5px]  hover:text-blue-950 hover:border-blue-950 {{ request()->routeIs('data_compare.transaction_detail.*') ? "text-blue-950 border-blue-950" : "text-gray-400" }}">{{ __("Transaction Detail") }}</a>
                    </li>
                </ul>
            </details>
        </li>

        <li>
            <details {{ request()->routeIs('data_compare.digital_receipt.*') ? "open" : ""}}>
                <summary class="hover:bg-yellow-400 p-1 pr-2 flex items-center justify-between text-base font-medium rounded-lg {{ request()->routeIs('data_compare.digital_receipt.*') ? "bg-yellow-400" : "" }}">
                    <a href="#" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-md bg-blue-950 flex justify-center items-center">
                        <i class="fa-solid fa-code-compare text-yellow-400"></i>
                        </div>
                        {{ __("Digital Receipt") }}
                    </a>
    
                    <i id="caret-menu" class="fa-solid fa-angle-up transition-all duration-100"></i>
                </summary>

                <ul id="submenu" class="ml-5 border-l-[1.5px] border-slate-200 mt-3 space-y-2">
                    <li>  
                        <a href="{{ route("data_compare.digital_receipt.dashboard") }}" class="text-base font-normal pl-5 border-l-[1.5px] -ml-[1.5px]  hover:text-blue-950 hover:border-blue-950 {{ request()->routeIs('data_compare.digital_receipt.*') ? "text-blue-950 border-blue-950" : "text-gray-400" }}">{{ __("Data Compare") }}</a>
                    </li>
                </ul>
            </details>
        </li>
    </ul>
  </div>
</aside>
