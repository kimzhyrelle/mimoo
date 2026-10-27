const MODAL_SELECTOR = '.confirm-modal-backdrop, .scan-modal-backdrop, .kyc-lightbox-backdrop';

let activeModal = null;
let openerElement = null;

function allModals() {
    return Array.from(document.querySelectorAll(MODAL_SELECTOR));
}

function resetInlineConfirm(modal) {
    modal.querySelector('[data-inline-confirm-panel]')?.remove();
    delete modal.dataset.confirming;

    modal.querySelectorAll('form[data-view-confirm]').forEach((form) => {
        delete form.dataset.confirmed;
    });
}

function resetModalFields(modal) {
    if (!modal.hasAttribute('data-reset-on-close')) {
        return;
    }

    modal.querySelectorAll('form').forEach((form) => form.reset());
}

function hideModal(modal) {
    if (!modal) {
        return;
    }

    resetInlineConfirm(modal);
    resetModalFields(modal);
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    delete modal.dataset.open;
}

function lockScroll() {
    document.body.style.overflow = 'hidden';
}

function unlockScroll() {
    document.body.style.overflow = '';
}

function visibleModals() {
    return allModals().filter((modal) => !modal.classList.contains('hidden'));
}

function firstFocusable(modal) {
    return modal.querySelector(
        '[data-autofocus], [autofocus], input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), a[href]'
    );
}

export function syncConfirmState(modal) {
    if (!modal) {
        return;
    }

    const button = modal.querySelector('[data-modal-confirm]');
    if (!button) {
        return;
    }

    const check = modal.querySelector('[data-confirm-check]');
    const requiredFields = Array.from(modal.querySelectorAll('[data-confirm-required]'));
    let complete = true;

    if (check) {
        complete = complete && check.checked;
    }

    requiredFields.forEach((field) => {
        complete = complete && String(field.value || '').trim() !== '';
    });

    button.disabled = !complete;
}

export function openModal(idOrElement, trigger) {
    const modal = typeof idOrElement === 'string'
        ? document.getElementById(idOrElement)
        : idOrElement;

    if (!modal) {
        return;
    }

    allModals().forEach((candidate) => {
        if (candidate !== modal) {
            hideModal(candidate);
        }
    });

    if (!activeModal || activeModal !== modal) {
        openerElement = trigger || document.activeElement;
    }

    activeModal = modal;
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    modal.dataset.open = '1';

    syncConfirmState(modal);
    lockScroll();

    const target = firstFocusable(modal);
    if (target) {
        window.setTimeout(() => {
            if (!modal.classList.contains('hidden')) {
                target.focus();
            }
        }, 40);
    }
}

export function closeModal(idOrElement) {
    const modal = typeof idOrElement === 'string'
        ? document.getElementById(idOrElement)
        : idOrElement;

    if (!modal) {
        return;
    }

    hideModal(modal);

    if (activeModal === modal || !activeModal) {
        activeModal = null;
    }

    if (visibleModals().length === 0) {
        unlockScroll();

        if (openerElement instanceof HTMLElement && openerElement.isConnected) {
            openerElement.focus();
        }

        openerElement = null;
    }
}

export function closeAllModals() {
    allModals().forEach(hideModal);
    activeModal = null;
    openerElement = null;
    unlockScroll();
}

export function updateConfirmButton(id) {
    syncConfirmState(document.getElementById(id));
}

function handleDocumentClick(event) {
    const closeTrigger = event.target.closest('[data-modal-close]');
    if (closeTrigger) {
        const modal = closeTrigger.closest(MODAL_SELECTOR);
        if (modal) {
            event.preventDefault();
            closeModal(modal);
            return;
        }
    }

    const openTrigger = event.target.closest('[data-modal-open]');
    if (openTrigger) {
        event.preventDefault();
        openModal(openTrigger.dataset.modalOpen, openTrigger);
        return;
    }

    if (event.target.matches(MODAL_SELECTOR)) {
        closeModal(event.target);
        return;
    }

    const confirmTrigger = event.target.closest('[data-modal-confirm]');
    if (!confirmTrigger || confirmTrigger.disabled) {
        return;
    }

    const modal = confirmTrigger.closest(MODAL_SELECTOR);
    if (!modal) {
        return;
    }

    if (confirmTrigger.type === 'submit') {
        return;
    }

    const formId = modal.dataset.confirmForm;
    if (formId && typeof confirmTrigger.onclick !== 'function') {
        closeModal(modal);
        document.getElementById(formId)?.submit();
    }
}

function handleDocumentChange(event) {
    const modal = event.target.closest(MODAL_SELECTOR);
    if (modal) {
        syncConfirmState(modal);
    }
}

function showInlineConfirm(modal, form, submitter) {
    const content = modal.querySelector('[data-view-content]');
    if (!content) {
        return false;
    }

    resetInlineConfirm(modal);

    const panel = document.createElement('div');
    panel.className = 'view-inline-confirm';
    panel.dataset.inlineConfirmPanel = '';
    panel.setAttribute('role', 'alertdialog');
    panel.setAttribute('aria-label', form.dataset.viewConfirmTitle || 'Confirm action');

    const heading = document.createElement('h3');
    heading.className = 'h-display text-[17px]';
    heading.textContent = form.dataset.viewConfirmTitle || 'Confirm action';

    const copy = document.createElement('p');
    copy.className = 'view-inline-confirm-copy';
    copy.textContent = form.dataset.viewConfirm || 'This action cannot be undone.';

    const actions = document.createElement('div');
    actions.className = 'view-inline-confirm-actions';

    const cancel = document.createElement('button');
    cancel.type = 'button';
    cancel.className = 'btn-ghost px-4 py-2.5';
    cancel.textContent = form.dataset.viewConfirmCancel || 'Cancel';

    const confirm = document.createElement('button');
    confirm.type = 'button';
    confirm.className = `${form.dataset.viewConfirmTone === 'primary' ? 'btn-primary' : 'btn-danger'} px-4 py-2.5`;
    confirm.textContent = form.dataset.viewConfirmLabel || 'Confirm';

    cancel.addEventListener('click', () => {
        resetInlineConfirm(modal);
        if (submitter instanceof HTMLElement) {
            submitter.focus();
        }
    });

    confirm.addEventListener('click', () => {
        resetInlineConfirm(modal);
        form.dataset.confirmed = '1';
        form.requestSubmit(submitter instanceof HTMLElement ? submitter : undefined);
        window.setTimeout(() => {
            delete form.dataset.confirmed;
        }, 0);
    });

    actions.append(cancel, confirm);
    panel.append(heading, copy, actions);
    content.before(panel);
    modal.dataset.confirming = '1';

    window.setTimeout(() => cancel.focus(), 0);

    return true;
}

function handleDocumentSubmit(event) {
    const form = event.target;
    const modal = form.closest(MODAL_SELECTOR);
    if (!modal) {
        return;
    }

    if (form.hasAttribute('data-view-confirm') && form.dataset.confirmed !== '1'
        && showInlineConfirm(modal, form, event.submitter)) {
        event.preventDefault();
        return;
    }

    const button = modal.querySelector('[data-modal-confirm], [data-view-primary]');
    if (button && !button.disabled) {
        button.dataset.loading = '1';
        button.disabled = true;
    }
}

function handleKeydown(event) {
    if (event.key !== 'Escape') {
        return;
    }

    const modal = activeModal || visibleModals().pop();
    if (modal) {
        event.preventDefault();
        closeModal(modal);
    }
}

function initialize() {
    closeAllModals();

    document.addEventListener('click', handleDocumentClick);
    document.addEventListener('change', handleDocumentChange);
    document.addEventListener('submit', handleDocumentSubmit);
    document.addEventListener('keydown', handleKeydown);
}

window.openModal = openModal;
window.closeModal = closeModal;
window.closeAllModals = closeAllModals;
window.syncConfirmState = syncConfirmState;
window.updateConfirmButton = updateConfirmButton;

// Backwards-compatible aliases used by existing Blade views.
window.openConfirmModal = openModal;
window.closeConfirmModal = closeModal;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
} else {
    initialize();
}
