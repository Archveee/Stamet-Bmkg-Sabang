<div class="relative min-h-screen flex items-center justify-center bg-slate-950 overflow-hidden select-none">

    <!-- Efek Lampu Neon Bulat di Latar Belakang (Ambient Glow) -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/20 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-cyan-600/20 rounded-full blur-[100px] pointer-events-none"></div>

    <!-- Garis Grid Digital Futuristik -->
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#0f172a_1px,transparent_1px),linear-gradient(to_bottom,#0f172a_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_50%,#000_70%,transparent_100%)] opacity-70 pointer-events-none"></div>

    <!-- Container Utama Form Login -->
    <div class="relative w-full max-w-md mx-4 z-10">

        <!-- Kotak Efek Kaca Melayang (Glassmorphism Card) -->
        <div class="bg-slate-900/60 backdrop-blur-xl border border-slate-800/80 p-8 rounded-2xl shadow-[0_0_50px_-12px_rgba(59,130,246,0.3)] hover:border-slate-700/50 transition-all duration-300">

            <!-- Bagian Logo & Judul Instansi -->
            <div class="flex flex-col items-center mb-8">
                <div class="w-20 h-20 mb-4 bg-slate-950/80 p-3 rounded-2xl border border-blue-500/30 flex items-center justify-center shadow-[0_0_20px_rgba(59,130,246,0.2)]">
                    <!-- Logo BMKG Otomatis -->
                    <img src="{{ asset('images/logo-bmkg.png') }}"
                         alt="Logo BMKG"
                         class="w-full h-full object-contain filter drop-shadow-[0_2px_8px_rgba(59,130,246,0.3)]"
                         onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/f/f0/Logo_BMKG_%282010%29.ashx.png';">
                </div>

                <h2 class="text-2xl font-black tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-cyan-400 to-indigo-400 uppercase">
                    BMKG BRIDGE
                </h2>
                <div class="h-[2px] w-12 bg-gradient-to-r from-blue-500 to-cyan-400 mt-2 rounded-full"></div>

                <p class="text-[10px] text-slate-400 mt-3 font-semibold tracking-widest uppercase">
                    Secure Enterprise Gateway
                </p>
            </div>

            <!-- Form Autentikasi Keamanan Filament -->
            <x-filament-panels::form wire:submit="authenticate">
                <div class="space-y-4 futuristic-form text-slate-200">
                    {{ $this->form }}
                </div>

                <div class="mt-8">
                    <x-filament-panels::form.actions
                        :actions="$this->getCachedFormActions()"
                        :full-width="$this->hasFullWidthFormActions()"
                    />
                </div>
            </x-filament-panels::form>
        </div>

        <!-- Footer Bawah -->
        <p class="text-center text-[9px] text-slate-600 mt-6 tracking-widest uppercase font-mono">
            &copy; {{ date('Y') }} BMKG Indonesia &bull; All Systems Operational
        </p>
    </div>
</div>

<style>
    .futuristic-form label {
        color: #94a3b8 !important;
        font-size: 0.75rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        font-weight: 600 !important;
    }

    .futuristic-form input {
        background-color: rgba(15, 23, 42, 0.7) !important;
        border-color: #1e293b !important;
        color: #f8fafc !important;
        border-radius: 0.5rem !important;
        transition: all 0.3s ease !important;
    }

    .futuristic-form input:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 15px rgba(59, 130, 246, 0.25) !important;
    }

    .fi-btn {
        background: linear-gradient(135deg, #2563eb, #0891b2) !important;
        border: none !important;
        box-shadow: 0 4px 20px rgba(8, 145, 178, 0.3) !important;
        transition: all 0.3s ease !important;
        font-weight: 750 !important;
        letter-spacing: 0.08em !important;
        text-transform: uppercase !important;
        border-radius: 0.5rem !important;
    }

    .fi-btn:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 25px rgba(37, 99, 235, 0.5) !important;
    }
</style>
