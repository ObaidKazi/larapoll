<x-filament-panels::page>
    @script
    <script>
        function pollResults(pollId) {
            return {
                init() {
                    if (window.Echo) {
                        this.subscribe();
                    } else {
                        window.addEventListener('echo-ready', () => this.subscribe());
                    }
                },
                subscribe() {
                    window.Echo.channel(`poll.${pollId}`)
                        .listen('.vote.cast', (e) => this.updateResults(e));
                },
                updateResults(data) {
                    const totalEl = document.getElementById('admin-total-votes');
                    if (totalEl) totalEl.textContent = data.total;

                    data.options.forEach(opt => {
                        const row   = document.querySelector(`[data-option-id="${opt.id}"]`);
                        if (!row) return;

                        const bar   = row.querySelector('.option-bar');
                        const pct   = row.querySelector('.option-pct');
                        const count = row.querySelector('.option-count');

                        if (bar)   bar.style.width   = opt.percentage + '%';
                        if (pct)   pct.textContent   = opt.percentage + '%';
                        if (count) count.textContent = `(${opt.votes_count})`;
                    });
                }
            }
        }
    </script>
    @endscript

</x-filament-panels::page>