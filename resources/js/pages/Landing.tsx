import { Link } from '@inertiajs/react';

export default function Landing() {
    return (
        <div>
            <h1>Welcome sa aking site</h1>
            <Link href="/register">
                <button>Register</button>
            </Link>
        </div>
    );
}
