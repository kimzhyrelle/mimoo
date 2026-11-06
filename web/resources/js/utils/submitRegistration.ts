type RegistrationResult =
    | { ok: true; redirectUrl: string }
    | { ok: false; errors: Record<string, string> };

function getCsrfToken(): string {
    return decodeURIComponent(
        document.cookie
            .split('; ')
            .find((row) => row.startsWith('XSRF-TOKEN='))
            ?.split('=')[1] ?? '',
    );
}

function appendFormValue(fd: FormData, key: string, value: unknown): void {
    if (value === null || value === undefined) {
        return;
    }

    if (value instanceof File) {
        fd.append(key, value);
        return;
    }

    if (typeof value === 'boolean') {
        fd.append(key, value ? '1' : '0');
        return;
    }

    if (value === '') {
        return;
    }

    fd.append(key, String(value));
}

/**
 * Submit registration outside Inertia's form helper so multipart uploads
 * can follow Laravel redirects reliably.
 */
export async function submitRegistration(
    data: Record<string, unknown>,
    email: string,
): Promise<RegistrationResult> {
    const fd = new FormData();

    for (const [key, value] of Object.entries(data)) {
        appendFormValue(fd, key, value);
    }

    const res = await fetch('/register', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin',
        headers: {
            'X-XSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
    });

    if (res.status === 422) {
        const payload = (await res.json()) as { errors?: Record<string, string | string[]> };
        const errors: Record<string, string> = {};

        for (const [key, value] of Object.entries(payload.errors ?? {})) {
            errors[key] = Array.isArray(value) ? value[0] : value;
        }

        return { ok: false, errors };
    }

    if (res.status === 419) {
        return {
            ok: false,
            errors: { _general: 'Session expired. Refresh the page and try again.' },
        };
    }

    if (!res.ok) {
        return {
            ok: false,
            errors: { _general: 'Registration failed. Please try again.' },
        };
    }

    let redirectUrl = `/verify-email?email=${encodeURIComponent(email)}`;

    try {
        const payload = (await res.json()) as { redirect?: string };
        if (payload.redirect) {
            redirectUrl = payload.redirect;
        }
    } catch {
        // Non-JSON success responses still redirect to OTP page.
    }

    return { ok: true, redirectUrl };
}
