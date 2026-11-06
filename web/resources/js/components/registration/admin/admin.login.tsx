import { useState, FormEvent } from "react";

export default function AdminLogin() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [emailError, setEmailError] = useState(false);
  const [passwordError, setPasswordError] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  const isValidEmail = (value: string) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);

  const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    const emailOk = isValidEmail(email.trim());
    const passwordOk = password.length > 0;

    setEmailError(!emailOk);
    setPasswordError(!passwordOk);

    if (!emailOk || !passwordOk) return;

    setSubmitting(true);
    // TODO: replace with real admin auth request
    setTimeout(() => {
      window.location.href = "/admin/dashboard";
    }, 700);
  };

  return (
    <div style={styles.page}>
      <div style={styles.card}>
        <div style={styles.brand}>
          <span style={styles.mark} aria-hidden="true">
            <svg width={16} height={16} viewBox="0 0 24 24" fill="none">
              <path d="M4 9L12 4L20 9V19C20 19.55 19.55 20 19 20H5C4.45 20 4 19.55 4 19V9Z" stroke="#18132a" strokeWidth={1.8} strokeLinejoin="round" />
              <path d="M9 20V13H15V20" stroke="#18132a" strokeWidth={1.8} strokeLinejoin="round" />
            </svg>
          </span>
          <span style={styles.brandName}>Mimoo Admin</span>
        </div>

        <span style={styles.portalTag}>
          <svg viewBox="0 0 24 24" fill="none" style={{ width: 11, height: 11 }}>
            <rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" strokeWidth={1.8} />
            <path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="currentColor" strokeWidth={1.8} />
          </svg>
          Admin portal
        </span>

        <h1 style={styles.h1}>Sign in to your account</h1>
        <p style={styles.lede}>Restricted access for authorized administrators only.</p>

        <form onSubmit={handleSubmit} noValidate style={styles.form}>
          <div>
            <label htmlFor="email" style={styles.label}>
              Admin email <span style={styles.req}>*</span>
            </label>
            <div style={styles.inputWrap}>
              <input
                type="email"
                id="email"
                name="email"
                placeholder="admin@mimoo.ph"
                autoComplete="username"
                required
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                style={styles.input}
              />
            </div>
            {emailError && <p style={styles.errorText}>Enter a valid admin email address.</p>}
          </div>

          <div>
            <label htmlFor="password" style={styles.label}>
              <span>
                Password <span style={styles.req}>*</span>
              </span>
              <a href="#" onClick={(e) => e.preventDefault()} style={styles.forgotLink}>
                Forgot password?
              </a>
            </label>
            <div style={styles.inputWrap}>
              <input
                type={showPassword ? "text" : "password"}
                id="password"
                name="password"
                placeholder="Enter your password"
                autoComplete="current-password"
                required
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                style={styles.input}
              />
              <button
                type="button"
                aria-label={showPassword ? "Hide password" : "Show password"}
                onClick={() => setShowPassword((v) => !v)}
                style={styles.toggleEye}
              >
                {showPassword ? (
                  <svg viewBox="0 0 24 24" fill="none" style={styles.eyeIcon}>
                    <path
                      d="M3 3l18 18M10.6 10.6a3 3 0 0 0 4.2 4.2M9.9 5.1A10.9 10.9 0 0 1 12 5c6.5 0 10 7 10 7a13.4 13.4 0 0 1-3.1 4.1M6.2 6.2C4 7.7 2 12 2 12s1.6 3.2 4.8 5.2"
                      stroke="currentColor"
                      strokeWidth={1.7}
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                  </svg>
                ) : (
                  <svg viewBox="0 0 24 24" fill="none" style={styles.eyeIcon}>
                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" stroke="currentColor" strokeWidth={1.7} strokeLinejoin="round" />
                    <circle cx="12" cy="12" r="3" stroke="currentColor" strokeWidth={1.7} />
                  </svg>
                )}
              </button>
            </div>
            {passwordError && <p style={styles.errorText}>Password is required.</p>}
          </div>

          <button type="submit" disabled={submitting} style={{ ...styles.submitBtn, opacity: submitting ? 0.65 : 1 }}>
            {submitting ? "Signing in…" : "Sign in"}
            {!submitting && (
              <svg viewBox="0 0 24 24" fill="none" style={{ width: 15, height: 15 }}>
                <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" />
              </svg>
            )}
          </button>
        </form>

        <div style={styles.divider}>
          <span style={styles.dividerLine} />
          <span style={styles.dividerText}>Need access?</span>
          <span style={styles.dividerLine} />
        </div>
        <p style={styles.footNote}>
          Admin accounts are created by IT.
          <br />
          <a href="#" onClick={(e) => e.preventDefault()} style={styles.footLink}>
            Contact support
          </a>{" "}
          to request one.
        </p>
      </div>
    </div>
  );
}

const colors = {
  deep: "#18132a",
  primary: "#513b80",
  primaryHover: "#402f66",
  accent: "#7b3fae",
  accentSoft: "#c3b3e0",
  lav50: "#f5f3fa",
  lav100: "#eeeaf7",
  lav200: "#ddd5ee",
  white: "#ffffff",
  ink: "#221c36",
  muted: "#6b637e",
  danger: "#b3384f",
};

const styles: Record<string, React.CSSProperties> = {
  page: {
    minHeight: "100vh",
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    padding: "2rem 1.25rem",
    background: `radial-gradient(700px 480px at 15% 0%, rgba(123,63,174,0.08), transparent 60%), ${colors.lav50}`,
    fontFamily: "'Inter', system-ui, sans-serif",
    color: colors.ink,
  },
  card: {
    width: "100%",
    maxWidth: 400,
    background: colors.white,
    border: `1.5px solid ${colors.lav200}`,
    borderRadius: 18,
    padding: "2.1rem 2rem 1.9rem",
    boxShadow: "0 24px 48px -20px rgba(81,59,128,0.16)",
  },
  brand: { display: "flex", alignItems: "center", gap: ".55rem", marginBottom: "1.6rem" },
  mark: {
    width: 32,
    height: 32,
    borderRadius: 9,
    flex: "none",
    background: `linear-gradient(155deg, ${colors.accentSoft}, ${colors.accent})`,
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
  },
  brandName: { fontFamily: "'Manrope', system-ui, sans-serif", fontWeight: 800, fontSize: ".92rem", color: colors.ink, letterSpacing: ".01em" },
  portalTag: {
    display: "inline-flex",
    alignItems: "center",
    gap: ".4rem",
    background: colors.lav100,
    border: `1px solid ${colors.lav200}`,
    borderRadius: 99,
    padding: ".32rem .8rem",
    fontSize: ".68rem",
    fontWeight: 700,
    letterSpacing: ".06em",
    color: colors.primary,
    textTransform: "uppercase",
    marginBottom: "1rem",
  },
  h1: { fontFamily: "'Manrope', system-ui, sans-serif", fontSize: "1.32rem", fontWeight: 800, color: colors.ink, marginBottom: ".45rem", marginTop: 0 },
  lede: { fontSize: ".84rem", color: colors.muted, marginBottom: "1.7rem" },
  form: { display: "flex", flexDirection: "column", gap: "1.15rem" },
  label: { display: "flex", alignItems: "baseline", justifyContent: "space-between", fontSize: ".8rem", fontWeight: 700, color: colors.ink, marginBottom: ".5rem" },
  req: { color: colors.danger, fontWeight: 700 },
  forgotLink: { fontSize: ".76rem", fontWeight: 600, color: colors.accent, textDecoration: "none" },
  inputWrap: { position: "relative" },
  input: {
    width: "100%",
    background: colors.white,
    border: `1.5px solid ${colors.lav200}`,
    borderRadius: 8,
    padding: ".78rem .9rem",
    fontSize: ".87rem",
    color: colors.ink,
    outline: "none",
  },
  toggleEye: {
    position: "absolute",
    right: ".65rem",
    top: "50%",
    transform: "translateY(-50%)",
    width: 28,
    height: 28,
    border: "none",
    background: "transparent",
    cursor: "pointer",
    color: colors.muted,
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    borderRadius: 6,
  },
  eyeIcon: { width: 17, height: 17 },
  errorText: { fontSize: ".76rem", color: colors.danger, marginTop: ".45rem" },
  submitBtn: {
    appearance: "none",
    border: "none",
    cursor: "pointer",
    width: "100%",
    background: colors.primary,
    color: "#fff",
    fontWeight: 700,
    fontSize: ".9rem",
    padding: ".85rem 1rem",
    borderRadius: 8,
    marginTop: ".2rem",
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    gap: ".5rem",
  },
  divider: { display: "flex", alignItems: "center", gap: ".7rem", margin: "1.7rem 0 1.1rem" },
  dividerLine: { flex: 1, height: 1, background: colors.lav200 },
  dividerText: { fontSize: ".68rem", fontWeight: 700, letterSpacing: ".08em", color: colors.muted, textTransform: "uppercase" },
  footNote: { fontSize: ".79rem", color: colors.muted, textAlign: "center", lineHeight: 1.55 },
  footLink: { color: colors.accent, fontWeight: 600, textDecoration: "underline" },
};