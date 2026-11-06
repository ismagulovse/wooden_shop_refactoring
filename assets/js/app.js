document.addEventListener('DOMContentLoaded', () => {
    const burgerBtn = document.getElementById('burgerBtn');
    const nav = document.getElementById('mainNav');
    const toggleFilters = document.getElementById('toggleFilters');
    const filters = document.getElementById('filtersPanel');

    if (burgerBtn && nav) {
        burgerBtn.addEventListener('click', () => nav.classList.toggle('open'));
    }

    if (toggleFilters && filters) {
        toggleFilters.addEventListener('click', () => filters.classList.toggle('open'));
    }

    document.querySelectorAll('.flash').forEach((flash) => {
        setTimeout(() => {
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 300);
        }, 3500);
    });

    document.querySelectorAll('form[data-validate]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (event.submitter && event.submitter.formNoValidate) return;
            const invalid = form.querySelector(':invalid');
            if (invalid) {
                event.preventDefault();
                invalid.focus();
            }
        });
    });

    document.querySelectorAll('.js-auto-qty-form .js-auto-qty').forEach((input) => {
        input.addEventListener('change', () => {
            const form = input.closest('form');
            if (form) {
                form.submit();
            }
        });
    });

    const downloadBtn = document.getElementById('downloadReceiptBtn');
    const receiptNode = document.getElementById('receiptToImage');

    if (downloadBtn && receiptNode) {
        downloadBtn.addEventListener('click', async () => {
            const originalText = downloadBtn.textContent;
            downloadBtn.disabled = true;
            downloadBtn.textContent = 'Подготовка...';

            try {
                if (typeof window.html2canvas === 'undefined') {
                    await new Promise((resolve, reject) => {
                        const script = document.createElement('script');
                        script.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
                        script.onload = resolve;
                        script.onerror = reject;
                        document.head.appendChild(script);
                    });
                }

                const canvas = await window.html2canvas(receiptNode, {
                    scale: 2,
                    backgroundColor: '#ffffff',
                    useCORS: true
                });

                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = `receipt-order-${Date.now()}.png`;
                link.click();
            } catch (err) {
                alert('Не удалось скачать чек как изображение.');
            } finally {
                downloadBtn.disabled = false;
                downloadBtn.textContent = originalText;
            }
        });
    }

    const formatTimer = (seconds) => {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    };

    document.querySelectorAll('.js-cancel-form').forEach((form) => {
        let remaining = parseInt(form.dataset.remaining || '0', 10);
        const timerNode = form.querySelector('.js-cancel-timer');
        const button = form.querySelector('button[type=\"submit\"]');

        const tick = () => {
            if (!timerNode || !button) return;

            if (remaining <= 0) {
                timerNode.textContent = 'Время истекло';
                button.disabled = true;
                button.classList.add('btn-light');
                return;
            }

            timerNode.textContent = `Отмена: ${formatTimer(remaining)}`;
            remaining -= 1;
            setTimeout(tick, 1000);
        };

        tick();
    });

    const homePage = document.querySelector('.home-page');
    if (homePage) {
        const revealNodes = homePage.querySelectorAll('.reveal');
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            });
        }, {
            threshold: 0.16,
            rootMargin: '0px 0px -8% 0px'
        });

        revealNodes.forEach((node) => observer.observe(node));
    }
});
