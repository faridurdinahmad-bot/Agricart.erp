/**
 * Agricart ERP — browser print helper for review documents.
 * Clones the review view to a body-level surface so modal scroll/height limits do not clip print output.
 */
const AgricartPrint = {
    review(delayMs = 0) {
        const run = () => {
            const source = document.querySelector('[data-agricart-print-document]');

            if (! source) {
                return;
            }

            document.getElementById('agricart-print-root')?.remove();

            const printRoot = source.cloneNode(true);
            printRoot.id = 'agricart-print-root';
            printRoot.setAttribute('data-agricart-print-surface', '');

            document.body.appendChild(printRoot);
            document.body.classList.add('agricart-print-active');

            const cleanup = () => {
                printRoot.remove();
                document.body.classList.remove('agricart-print-active');
                window.removeEventListener('afterprint', cleanup);
            };

            window.addEventListener('afterprint', cleanup);

            requestAnimationFrame(() => {
                window.print();
            });
        };

        if (delayMs > 0) {
            window.setTimeout(run, delayMs);

            return;
        }

        run();
    },
};

window.AgricartPrint = AgricartPrint;
