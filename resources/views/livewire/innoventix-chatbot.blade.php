<div>
    @if (auth()->check() && auth()->user()->hasRole('admin'))
        <!-- Floating Toggle Button -->
        <button 
            type="button"
            wire:click="toggleChat"
            class="flex items-center justify-center w-14 h-14 rounded-full bg-primary-600 hover:bg-primary-500 text-white shadow-lg transition-transform duration-300 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
            style="position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;"
            aria-label="Toggle AI Chatbot"
        >
            @if ($isOpen)
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            @else
                <div class="relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <span class="absolute top-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-primary-600 bg-emerald-400 animate-ping"></span>
                    <span class="absolute top-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-primary-600 bg-emerald-400"></span>
                </div>
            @endif
        </button>

        <!-- Chat Card Panel (Styled using Inline CSS for Guaranteed Color Rendering) -->
        <div 
            x-data="{ 
                init() {
                    $watch('$wire.isOpen', value => {
                        if (value) {
                            this.scrollToBottom();
                            setTimeout(() => this.focusInput(), 100);
                        }
                    });
                    
                    window.addEventListener('scroll-chat-to-bottom', () => {
                        this.scrollToBottom();
                    });
                    window.addEventListener('focus-chat-input', () => {
                        this.focusInput();
                    });
                },
                scrollToBottom() {
                    const el = this.$refs.chatBody;
                    if (el) {
                        el.scrollTop = el.scrollHeight;
                    }
                },
                focusInput() {
                    const input = this.$refs.chatInput;
                    if (input) {
                        input.focus();
                    }
                }
            }"
            x-show="$wire.isOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="rounded-2xl shadow-2xl flex flex-col overflow-hidden"
            style="position: fixed; bottom: 5.5rem; right: 1.5rem; z-index: 9999; width: 420px; max-width: calc(100vw - 3rem); height: 580px; max-height: calc(100vh - 8rem); background-color: #0f172a; border: 1px solid #1e293b; color: #f8fafc; display: none;"
        >
            <!-- Header -->
            <div class="px-4 py-3 flex items-center justify-between shadow-md" style="background: linear-gradient(135deg, #4f46e5, #6366f1); color: #ffffff;">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-sm leading-tight text-white">Innoventix AI Assistant</h3>
                        <p class="text-[10px] text-white/70">Database Explorer Powered by Gemini</p>
                    </div>
                </div>
                <div class="flex items-center space-x-1">
                    <button 
                        type="button" 
                        wire:click="clearChat" 
                        class="p-1 rounded-lg hover:bg-white/10 text-white transition-colors"
                        title="Clear Conversation"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                    <button 
                        type="button" 
                        wire:click="toggleChat" 
                        class="p-1 rounded-lg hover:bg-white/10 text-white transition-colors"
                        title="Minimize"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Chat Body / Messages -->
            <div 
                x-ref="chatBody"
                class="flex-1 overflow-y-auto p-4 space-y-4"
                style="background-color: #090d16;"
            >
                @foreach ($history as $msg)
                    <div class="flex {{ $msg['type'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] flex flex-col space-y-1">
                            <!-- Message bubble -->
                            <div class="rounded-2xl px-4 py-2.5 text-xs shadow-sm leading-relaxed
                                {{ $msg['type'] === 'user' ? 'rounded-tr-none' : 'rounded-tl-none' }}"
                                style="
                                    @if ($msg['type'] === 'user')
                                        background-color: #4f46e5; color: #ffffff;
                                    @elseif ($msg['type'] === 'error')
                                        background-color: rgba(225, 29, 72, 0.15); color: #fda4af; border: 1px solid rgba(225, 29, 72, 0.3);
                                    @else
                                        background-color: #1e293b; color: #f8fafc; border: 1px solid #334155;
                                    @endif
                                "
                            >
                                <p class="whitespace-pre-wrap">{{ $msg['message'] }}</p>
                            </div>

                            <!-- Secondary content (SQL / Tables) for Bot Response -->
                            @if ($msg['type'] === 'bot')
                                <!-- SQL Expander -->
                                @if (!empty($msg['sql']))
                                    <div x-data="{ showSql: false }" class="mt-1">
                                        <button 
                                            type="button" 
                                            @click="showSql = !showSql"
                                            class="flex items-center space-x-1 text-[10px] font-medium hover:underline focus:outline-none"
                                            style="color: #818cf8;"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                            </svg>
                                            <span x-text="showSql ? 'Hide generated SQL' : 'Show generated SQL'"></span>
                                        </button>
                                        <div 
                                            x-show="showSql" 
                                            x-transition 
                                            class="mt-1.5 p-2 rounded-lg font-mono text-[9px] overflow-x-auto leading-normal select-all shadow-inner"
                                            style="display: none; background-color: #020617; color: #cbd5e1; border: 1px solid #1e293b;"
                                        >
                                            {{ $msg['sql'] }}
                                        </div>
                                    </div>
                                @endif

                                <!-- Results Table -->
                                @if (!empty($msg['rows']) && is_array($msg['rows']))
                                    <div class="mt-2 overflow-x-auto rounded-lg shadow-sm max-w-full" style="border: 1px solid #1e293b; background-color: #0f172a;">
                                        <table class="min-w-full divide-y text-[10px]" style="divide-color: #1e293b;">
                                            <thead style="background-color: #020617;">
                                                <tr>
                                                    @foreach (array_keys($msg['rows'][0]) as $colHeader)
                                                        @if ($colHeader !== 'image_url')
                                                            <th scope="col" class="px-2 py-1 text-left font-medium uppercase tracking-wider" style="color: #94a3b8;">
                                                                {{ $colHeader }}
                                                            </th>
                                                        @endif
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y" style="divide-color: #1e293b;">
                                                @foreach ($msg['rows'] as $index => $row)
                                                    <tr style="background-color: {{ $index % 2 === 0 ? '#0f172a' : '#090d16' }};">
                                                        @foreach ($row as $key => $val)
                                                            @if ($key !== 'image_url')
                                                                <td class="px-2 py-1 whitespace-nowrap" style="color: #f1f5f9;">
                                                                    {{ is_array($val) ? json_encode($val) : $val }}
                                                                </td>
                                                            @endif
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach

                <!-- Typing/Loading Indicator -->
                @if ($isLoading)
                    <div class="flex justify-start">
                        <div class="rounded-2xl rounded-tl-none px-4 py-3 shadow-sm flex items-center space-x-1.5" style="background-color: #1e293b; border: 1px solid #334155;">
                            <span class="w-2 h-2 rounded-full animate-bounce" style="background-color: #818cf8; animation-delay: 0ms"></span>
                            <span class="w-2 h-2 rounded-full animate-bounce" style="background-color: #818cf8; animation-delay: 150ms"></span>
                            <span class="w-2 h-2 rounded-full animate-bounce" style="background-color: #818cf8; animation-delay: 300ms"></span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer / Input form -->
            <div class="p-3 border-t" style="background-color: #0f172a; border-color: #1e293b;">
                <form wire:submit.prevent="ask" class="flex items-center space-x-2">
                    <input 
                        type="text" 
                        x-ref="chatInput"
                        wire:model.defer="prompt"
                        placeholder="Ask a database question..." 
                        class="flex-1 rounded-xl text-xs px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        style="background-color: #020617; border: 1px solid #1e293b; color: #f8fafc;"
                        required
                        @keydown.escape="$wire.toggleChat"
                    >
                    <button 
                        type="submit" 
                        wire:loading.attr="disabled"
                        class="flex items-center justify-center p-2.5 rounded-xl text-white focus:outline-none disabled:opacity-50 transition-colors"
                        style="background-color: #4f46e5;"
                        onmouseover="this.style.backgroundColor='#4338ca'"
                        onmouseout="this.style.backgroundColor='#4f46e5'"
                    >
                        <svg class="w-4.5 h-4.5 transform rotate-90 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    @else
        <!-- hidden -->
    @endif
</div>
