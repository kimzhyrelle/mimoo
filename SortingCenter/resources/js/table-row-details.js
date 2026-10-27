function readableCellValue(cell) {
    const content = cell.cloneNode(true);

    content.querySelectorAll('button, form, input[type="checkbox"], svg, [aria-hidden="true"]').forEach((element) => {
        element.remove();
    });

    content.querySelectorAll('select').forEach((select) => {
        const selectedText = Array.from(select.selectedOptions)
            .map((option) => option.textContent.trim())
            .filter(Boolean)
            .join(', ');
        select.replaceWith(document.createTextNode(selectedText));
    });

    content.querySelectorAll('input:not([type="checkbox"]), textarea').forEach((field) => {
        field.replaceWith(document.createTextNode(field.value || ''));
    });

    return content.textContent.replace(/\s+/g, ' ').trim();
}

/**
 * Adds a non-destructive "View" action to data tables. The table is never
 * mutated apart from appending the View button, and modal actions are clones.
 * This prevents row controls (carrier/rider selects, forms) from leaking into
 * the table or disappearing after the modal is closed.
 */
function initializeTableRowDetails() {
    const modal = document.getElementById('rowDetailsModal');
    const modalTitle = document.getElementById('rowDetailsModalTitle');
    const modalContent = document.getElementById('rowDetailsModalContent');
    const modalActions = document.getElementById('rowDetailsModalActions');
    const modalActionButtons = document.getElementById('rowDetailsModalActionButtons');
    if (!modal || !modalTitle || !modalContent || !modalActions || !modalActionButtons) {
        return;
    }

    const rowEntries = new WeakMap();
    const rowActions = new WeakMap();

    document.querySelectorAll('main table, .app-shell-content table').forEach((table) => {
        if (table.dataset.rowDetails === 'off') {
            return;
        }

        const headerRow = table.tHead?.rows[table.tHead.rows.length - 1];
        if (!headerRow) {
            return;
        }

        const headers = Array.from(headerRow.cells);
        if (!headers.length) {
            return;
        }

        let actionIndex = headers.findIndex((header) => /^actions?$/i.test(header.textContent.trim()));
        if (actionIndex === -1) {
            const actionHeader = document.createElement('th');
            actionHeader.className = 'text-right';
            actionHeader.textContent = 'Action';
            headerRow.append(actionHeader);
            actionIndex = headers.length;
            headers.push(actionHeader);
        }

        Array.from(table.tBodies).flatMap((body) => Array.from(body.rows)).forEach((row) => {
            if (row.cells.length === 1 && row.cells[0].hasAttribute('colspan')) {
                return;
            }
            if (row.cells.length !== headers.length && row.cells.length !== headers.length - 1) {
                return;
            }
            if (row.querySelector('[data-row-details], [data-modal-open]')) {
                return;
            }

            let actionCell = row.cells[actionIndex];
            if (!actionCell) {
                actionCell = document.createElement('td');
                actionCell.className = 'text-right';
                row.append(actionCell);
            }

            const viewButton = document.createElement('button');
            viewButton.type = 'button';
            viewButton.className = 'btn-ghost row-details-trigger px-3 py-1.5 text-[12px]';
            viewButton.dataset.rowDetails = 'true';
            viewButton.setAttribute('aria-haspopup', 'dialog');
            viewButton.setAttribute('aria-label', 'View row details');
            viewButton.textContent = 'View';
            actionCell.append(viewButton);
        });
    });

    function entriesFor(row) {
        const table = row.closest('table');
        const headerRow = table?.tHead?.rows[table.tHead.rows.length - 1];
        const headers = headerRow ? Array.from(headerRow.cells) : [];
        const entries = headers.flatMap((header, index) => {
            if (/^actions?$/i.test(header.textContent.trim()) || !row.cells[index]) {
                return [];
            }
            const value = readableCellValue(row.cells[index]);
            return value ? [{ label: header.textContent.trim(), value }] : [];
        });

        if (row.dataset.detailStatus) {
            entries.push({ label: 'Status', value: row.dataset.detailStatus });
        }
        if (row.dataset.detailLastScan) {
            entries.push({ label: 'Last scan', value: row.dataset.detailLastScan });
        }

        return entries;
    }

    function actionsFor(row) {
        const candidates = Array.from(row.querySelectorAll(
            'form, a[href], button, select, input:not([type="checkbox"]), textarea'
        ));

        return candidates
            .filter((element) => !element.matches('[data-row-details]'))
            .filter((element) => !element.matches('[data-modal-close]'))
            .filter((element) => !(element.closest('form') && !element.matches('form')))
            .filter((element) => !(element.closest('a[href]') && !element.matches('a[href]')))
            .map((element) => {
                const clone = element.cloneNode(true);
                if (clone.matches('form')) {
                    clone.querySelectorAll('[data-row-details]').forEach((node) => node.remove());
                }
                return clone;
            })
            .filter((clone) => {
                if (clone.matches('form')) {
                    return clone.querySelectorAll('input, select, button, textarea').length > 0;
                }
                return clone.matches('a[href], button') && clone.textContent.trim() !== '';
            });
    }

    function closeModal() {
        modalActionButtons.replaceChildren();
        modalActions.classList.add('hidden');
    }

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-row-details]');
        if (button) {
            const row = button.closest('tr');
            if (!row) {
                return;
            }

            let entries = rowEntries.get(row);
            let actions = rowActions.get(row);
            if (!entries || !actions) {
                entries = entriesFor(row);
                actions = actionsFor(row);
                rowEntries.set(row, entries);
                rowActions.set(row, actions);
            }

            modalTitle.textContent = `View - ${entries[0]?.value || 'row'}`;
            modalContent.replaceChildren();
            entries.forEach(({ label, value }) => {
                const term = document.createElement('dt');
                term.textContent = label;
                const description = document.createElement('dd');
                description.textContent = value;
                modalContent.append(term, description);
            });

            modalActionButtons.replaceChildren();
            actions.forEach((element) => modalActionButtons.append(element.cloneNode(true)));
            modalActions.classList.toggle('hidden', modalActionButtons.childElementCount === 0);
            window.openModal?.(modal, button);
            return;
        }

        if (event.target.closest('[data-modal-close]') || event.target === modal || event.target.closest('[data-modal-open]')) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeTableRowDetails);
} else {
    initializeTableRowDetails();
}
