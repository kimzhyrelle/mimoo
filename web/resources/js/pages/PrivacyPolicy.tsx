import { Link } from '@inertiajs/react';
import { ReactNode } from 'react';

const fontSyncopate = { fontFamily: 'Syncopate, sans-serif' };
const fontPoppins = { fontFamily: 'Poppins, sans-serif' };

export default function PrivacyPolicy() {
    return (
        <div className="min-h-screen bg-white" style={fontPoppins}>
            {/* Header */}
            <nav className="bg-white border-b border-gray-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-3.5 flex items-center justify-between">
                    <Link href="/" className="flex items-center gap-2">
                        <span className="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-[#3B1F6B]" />
                        <span className="font-bold tracking-widest text-sm text-gray-900" style={fontSyncopate}>
                            mimoo
                        </span>
                    </Link>
                    <Link href="/" className="text-sm font-medium text-gray-700 hover:text-[#3B1F6B] transition">
                        Back to Home
                    </Link>
                </div>
            </nav>

            {/* Content */}
            <div className="max-w-4xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
                <h1 className="text-3xl sm:text-4xl font-bold text-gray-900 mb-4" style={fontSyncopate}>
                    Privacy Policy
                </h1>
                <p className="text-sm text-gray-500 mb-8">
                    Last updated: {new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}
                </p>

                <div className="prose prose-gray max-w-none">
                    <section className="mb-8">
                        <h2 className="text-xl font-bold text-gray-900 mb-3">1. Introduction</h2>
                        <p className="text-gray-700 leading-relaxed mb-3">
                            Welcome to mimoo. We respect your privacy and are committed to protecting your personal data. 
                            This privacy policy will inform you about how we look after your personal data when you visit 
                            our platform and tell you about your privacy rights and how the law protects you.
                        </p>
                    </section>

                    <section className="mb-8">
                        <h2 className="text-xl font-bold text-gray-900 mb-3">2. Information We Collect</h2>
                        <p className="text-gray-700 leading-relaxed mb-3">
                            We collect the following information when you register and use our services:
                        </p>
                        <ul className="list-disc pl-6 text-gray-700 space-y-2 mb-3">
                            <li><strong>Personal Information:</strong> Name, email address, mobile number, date of birth, and sex</li>
                            <li><strong>Address Information:</strong> Province, city/municipality, barangay, and street address</li>
                            <li><strong>Identity Verification:</strong> Government-issued ID documents</li>
                            <li><strong>Business Information:</strong> For sellers and logistics partners - business name, line of business, and business permits</li>
                            <li><strong>Transaction Data:</strong> Purchase history, payment information, and order details</li>
                            <li><strong>Technical Data:</strong> IP address, browser type, device information, and usage data</li>
                        </ul>
                    </section>

                    <section className="mb-8">
                        <h2 className="text-xl font-bold text-gray-900 mb-3">3. How We Use Your Information</h2>
                        <p className="text-gray-700 leading-relaxed mb-3">
                            We use your information for the following purposes:
                        </p>
                        <ul className="list-disc pl-6 text-gray-700 space-y-2 mb-3">
                            <li>To create and manage your account</li>
                            <li>To verify your identity and prevent fraud</li>
                            <li>To process your orders and payments</li>
                            <li>To facilitate communication between buyers, sellers, and logistics partners</li>
                            <li>To send you order updates and important notifications</li>
                            <li>To improve our services and user experience</li>
                            <li>To comply with legal obligations</li>
                        </ul>
                    </section>

                    <section className="mb-8">
                        <h2 className="text-xl font-bold text-gray-900 mb-3">4. How We Share Your Information</h2>
                        <p className="text-gray-700 leading-relaxed mb-3">
                            We may share your information with:
                        </p>
                        <ul className="list-disc pl-6 text-gray-700 space-y-2 mb-3">
                            <li><strong>Sellers:</strong> When you place an order, we share your delivery address and contact information with the seller</li>
                            <li><strong>Logistics Partners:</strong> For order fulfillment and delivery</li>
                            <li><strong>Payment Processors:</strong> To process your payments securely</li>
                            <li><strong>Service Providers:</strong> Who help us operate our platform</li>
                            <li><strong>Legal Authorities:</strong> When required by law or to protect our rights</li>
                        </ul>
                        <p className="text-gray-700 leading-relaxed">
                            We do not sell your personal information to third parties.
                        </p>
                    </section>

                    <section className="mb-8">
                        <h2 className="text-xl font-bold text-gray-900 mb-3">5. Data Security</h2>
                        <p className="text-gray-700 leading-relaxed mb-3">
                            We implement appropriate technical and organizational measures to protect your personal data against 
                            unauthorized access, alteration, disclosure, or destruction. These measures include:
                        </p>
                        <ul className="list-disc pl-6 text-gray-700 space-y-2 mb-3">
                            <li>Encryption of sensitive data</li>
                            <li>Secure password storage</li>
                            <li>Regular security assessments</li>
                            <li>Access controls and authentication</li>
                            <li>Secure file storage for uploaded documents</li>
                        </ul>
                    </section>

                    <section className="mb-8">
                        <h2 className="text-xl font-bold text-gray-900 mb-3">6. Your Rights</h2>
                        <p className="text-gray-700 leading-relaxed mb-3">
                            Under the Data Privacy Act of 2012 (Republic Act No. 10173), you have the right to:
                        </p>
                        <ul className="list-disc pl-6 text-gray-700 space-y-2 mb-3">
                            <li>Access your personal data</li>
                            <li>Request correction of inaccurate data</li>
                            <li>Request deletion of your data (subject to legal requirements)</li>
                            <li>Object to processing of your data</li>
                            <li>Request data portability</li>
                            <li>Withdraw consent at any time</li>
                        </ul>
                    </section>

                    <section className="mb-8">
                        <h2 className="text-xl font-bold text-gray-900 mb-3">7. Cookies and Tracking</h2>
                        <p className="text-gray-700 leading-relaxed mb-3">
                            We use cookies and similar tracking technologies to improve your experience on our platform. 
                            Cookies help us remember your preferences, understand how you use our site, and provide personalized features.
                        </p>
                    </section>

                    <section className="mb-8">
                        <h2 className="text-xl font-bold text-gray-900 mb-3">8. Data Retention</h2>
                        <p className="text-gray-700 leading-relaxed mb-3">
                            We retain your personal data only for as long as necessary to fulfill the purposes outlined in this 
                            privacy policy, unless a longer retention period is required or permitted by law. When your data is 
                            no longer needed, we will securely delete or anonymize it.
                        </p>
                    </section>

                    <section className="mb-8">
                        <h2 className="text-xl font-bold text-gray-900 mb-3">9. Children's Privacy</h2>
                        <p className="text-gray-700 leading-relaxed mb-3">
                            Our services are not intended for individuals under 18 years of age. We do not knowingly collect 
                            personal information from children. All users must be at least 18 years old to register.
                        </p>
                    </section>

                    <section className="mb-8">
                        <h2 className="text-xl font-bold text-gray-900 mb-3">10. Changes to This Policy</h2>
                        <p className="text-gray-700 leading-relaxed mb-3">
                            We may update this privacy policy from time to time. We will notify you of any significant changes 
                            by posting the new policy on this page and updating the "Last updated" date. Your continued use of 
                            our services after changes constitutes acceptance of the updated policy.
                        </p>
                    </section>

                    <section className="mb-8">
                        <h2 className="text-xl font-bold text-gray-900 mb-3">11. Contact Us</h2>
                        <p className="text-gray-700 leading-relaxed mb-3">
                            If you have any questions, concerns, or requests regarding this privacy policy or our data practices, 
                            please contact us at:
                        </p>
                        <div className="bg-[#F8F6FC] rounded-lg p-4 text-gray-700">
                            <p className="mb-1"><strong>Email:</strong> privacy@mimoo.ph</p>
                            <p className="mb-1"><strong>Phone:</strong> +63 XXX XXX XXXX</p>
                            <p><strong>Address:</strong> [Your Business Address]</p>
                        </div>
                    </section>
                </div>

                <div className="mt-12 pt-8 border-t border-gray-200">
                    <p className="text-sm text-gray-500 text-center">
                        By using mimoo, you agree to this Privacy Policy and our{' '}
                        <Link href="/terms" className="text-[#3B1F6B] font-medium hover:underline">
                            Terms of Service
                        </Link>
                        .
                    </p>
                </div>
            </div>
        </div>
    );
}

PrivacyPolicy.layout = (page: ReactNode) => page;
