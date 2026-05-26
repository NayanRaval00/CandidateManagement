<div wire:poll.10s="checkNotifications" style="display: none;">
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('play-notification-sound', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                
                // 1. Play Synthesized Double-Note Chime Sound
                try {
                    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                    if (AudioContextClass) {
                        const audioCtx = new AudioContextClass();
                        
                        const playNote = (frequency, startTime, duration) => {
                            const osc = audioCtx.createOscillator();
                            const gain = audioCtx.createGain();
                            
                            osc.type = 'sine';
                            osc.frequency.setValueAtTime(frequency, startTime);
                            
                            gain.gain.setValueAtTime(0.08, startTime);
                            gain.gain.exponentialRampToValueAtTime(0.001, startTime + duration);
                            
                            osc.connect(gain);
                            gain.connect(audioCtx.destination);
                            
                            osc.start(startTime);
                            osc.stop(startTime + duration);
                        };
                        
                        const now = audioCtx.currentTime;
                        playNote(659.25, now, 0.25); // E5
                        playNote(880.00, now + 0.12, 0.35); // A5
                    }
                } catch (e) {
                    console.warn("Failed to play notification chime:", e);
                }

                // 2. Trigger Filament Toast
                try {
                    if (window.FilamentNotification) {
                        new window.FilamentNotification()
                            .title(data.title)
                            .body(data.body)
                            .icon(data.icon || 'heroicon-o-bell')
                            .status(data.color || 'info')
                            .color(data.color || 'info')
                            .send();
                    }
                } catch (e) {
                    console.error("Failed to display Filament toast:", e);
                }
            });
        });
    </script>
</div>
