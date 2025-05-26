<!-- navbar -->
<div class="bg-white fixed max-h-14 inset-0 md:left-[300px] shadow-sm z-50">
    <div class="mx-auto px-4 py-2 flex items-center justify-between bg-transparent">
        <!-- title -->
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-bars font-bold text-lg cursor-pointer md:invisible px-3 py-2 hover:bg-slate-100 focus:ring-4 focus:ring-slate-300 rounded-lg"  id="hamburger"></i>
            <a href="#" class="text-lg font-bold">Mediasi Data</a>
            <span>{{ config('app.version') }}</span>
        </div>
  
        <!-- menu -->
        <button id="profile" class="outline-none hover:bg-border-100 rounded-lg flex items-center text-medium font-medium mr-10">
            <div class="w-10 overflow-hidden rounded-full">
                <img
                    alt="username"
                    src="https://ui-avatars.com/api/?name={{ str_replace(" ", "+", auth()->user()->name) }}&background=FACC15"
                />
            </div>
        </button>
        
        <nav class="bg-white absolute max-w-[150px] w-full top-16 shadow-lg right-10 rounded-lg hidden">
            <ul class="flex flex-col">
                {{-- <li class="my-2 px-4 group hover:bg-gray-200">
                    <a href="{{ route("profile.dashboard") }}" class="font-normal text-base lg:text-base md:text-base flex gap-2 items-center">
                        <i class="fa-solid fa-user text-xs md:text-md "></i> Profile
                    </a>
                </li>
                <hr> --}}
                <li class="my-2 px-4 group hover:bg-gray-200">
                    <form action="{{ route('auth.logout') }}" method="POST">
                    @csrf
                        <button class="text-base font-normal flex gap-2 items-center">
                            <i class="fa-solid fa-power-off text-red-500 text-xs md:text-md"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </nav>
    </div>
</div>