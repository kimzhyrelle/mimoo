import { Head } from '@inertiajs/react';
import { BadgeCheck, Bell, Lock, Mail, Package, ShieldCheck, Store, Wallet } from 'lucide-react';
import SellerShell from '@/layouts/seller/seller-shell';

const fontSyne = { fontFamily: 'Syne, sans-serif' };
const fontPoppins = { fontFamily: 'Poppins, sans-serif' };

export default function SellerSettings() {
    return (
        <SellerShell active="settings">
            <Head title="Account Settings" />

            {/* Page Header */}
            <div className="mb-8">
                <p className="text-xs font-extrabold text-[#4B2E7E] uppercase tracking-wider mb-1">SETTINGS</p>
                <h1 className="text-[1.8rem] font-bold leading-tight" style={fontSyne}>Account Settings</h1>
                <p className="text-sm text-[#6E6570] mt-1">Manage your store profile, business verification, and account security.</p>
            </div>

            {/* Settings Grid */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
                {/* Store Profile Card */}
                <div className="bg-white rounded-xl border-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)] p-6 hover:border-[#B9A6DE] hover:shadow-md transition-all cursor-pointer">
                    <div className="flex items-start gap-4">
                        <div className="w-12 h-12 rounded-xl bg-[color-mix(in_srgb,#B9A6DE_24%,white)] text-[#4B2E7E] flex items-center justify-center flex-shrink-0">
                            <Store className="w-6 h-6" />
                        </div>
                        <div className="flex-1 min-w-0">
                            <h3 className="text-base font-semibold mb-1" style={fontSyne}>Store Profile</h3>
                            <p className="text-sm text-[#6E6570] leading-relaxed mb-3">
                                Update your store name, logo, description, and pickup address shown to buyers.
                            </p>
                            <button className="text-sm font-bold text-[#4B2E7E] hover:underline">
                                Edit profile →
                            </button>
                        </div>
                    </div>
                </div>

                {/* Business Verification Card */}
                <div className="bg-white rounded-xl border-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)] p-6 hover:border-[#B9A6DE] hover:shadow-md transition-all cursor-pointer">
                    <div className="flex items-start gap-4">
                        <div className="w-12 h-12 rounded-xl bg-[color-mix(in_srgb,#B9A6DE_24%,white)] text-[#4B2E7E] flex items-center justify-center flex-shrink-0">
                            <ShieldCheck className="w-6 h-6" />
                        </div>
                        <div className="flex-1 min-w-0">
                            <div className="flex items-center gap-2 mb-1">
                                <h3 className="text-base font-semibold" style={fontSyne}>Business Verification</h3>
                                <BadgeCheck className="w-5 h-5 text-[#2c7a52]" />
                            </div>
                            <p className="text-sm text-[#6E6570] leading-relaxed mb-3">
                                Your business is verified. Upload new documents to update verification.
                            </p>
                            <button className="text-sm font-bold text-[#4B2E7E] hover:underline">
                                View documents →
                            </button>
                        </div>
                    </div>
                </div>

                {/* Account Security Card */}
                <div className="bg-white rounded-xl border-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)] p-6 hover:border-[#B9A6DE] hover:shadow-md transition-all cursor-pointer">
                    <div className="flex items-start gap-4">
                        <div className="w-12 h-12 rounded-xl bg-[color-mix(in_srgb,#B9A6DE_24%,white)] text-[#4B2E7E] flex items-center justify-center flex-shrink-0">
                            <Lock className="w-6 h-6" />
                        </div>
                        <div className="flex-1 min-w-0">
                            <h3 className="text-base font-semibold mb-1" style={fontSyne}>Account Security</h3>
                            <p className="text-sm text-[#6E6570] leading-relaxed mb-3">
                                Change your password, update email address, and enable two-factor authentication.
                            </p>
                            <button className="text-sm font-bold text-[#4B2E7E] hover:underline">
                                Manage security →
                            </button>
                        </div>
                    </div>
                </div>

                {/* Payment & Payout Card */}
                <div className="bg-white rounded-xl border-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)] p-6 hover:border-[#B9A6DE] hover:shadow-md transition-all cursor-pointer">
                    <div className="flex items-start gap-4">
                        <div className="w-12 h-12 rounded-xl bg-[color-mix(in_srgb,#B9A6DE_24%,white)] text-[#4B2E7E] flex items-center justify-center flex-shrink-0">
                            <Wallet className="w-6 h-6" />
                        </div>
                        <div className="flex-1 min-w-0">
                            <h3 className="text-base font-semibold mb-1" style={fontSyne}>Payment & Payout</h3>
                            <p className="text-sm text-[#6E6570] leading-relaxed mb-3">
                                Manage your bank accounts, e-wallets, and payout settings for your earnings.
                            </p>
                            <button className="text-sm font-bold text-[#4B2E7E] hover:underline">
                                Setup payouts →
                            </button>
                        </div>
                    </div>
                </div>

                {/* Notifications Card */}
                <div className="bg-white rounded-xl border-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)] p-6 hover:border-[#B9A6DE] hover:shadow-md transition-all cursor-pointer">
                    <div className="flex items-start gap-4">
                        <div className="w-12 h-12 rounded-xl bg-[color-mix(in_srgb,#B9A6DE_24%,white)] text-[#4B2E7E] flex items-center justify-center flex-shrink-0">
                            <Bell className="w-6 h-6" />
                        </div>
                        <div className="flex-1 min-w-0">
                            <h3 className="text-base font-semibold mb-1" style={fontSyne}>Notifications</h3>
                            <p className="text-sm text-[#6E6570] leading-relaxed mb-3">
                                Control email, SMS, and push notifications for orders, messages, and updates.
                            </p>
                            <button className="text-sm font-bold text-[#4B2E7E] hover:underline">
                                Manage notifications →
                            </button>
                        </div>
                    </div>
                </div>

                {/* Contact Information Card */}
                <div className="bg-white rounded-xl border-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)] p-6 hover:border-[#B9A6DE] hover:shadow-md transition-all cursor-pointer">
                    <div className="flex items-start gap-4">
                        <div className="w-12 h-12 rounded-xl bg-[color-mix(in_srgb,#B9A6DE_24%,white)] text-[#4B2E7E] flex items-center justify-center flex-shrink-0">
                            <Mail className="w-6 h-6" />
                        </div>
                        <div className="flex-1 min-w-0">
                            <h3 className="text-base font-semibold mb-1" style={fontSyne}>Contact Information</h3>
                            <p className="text-sm text-[#6E6570] leading-relaxed mb-3">
                                Update your email address and phone number for account recovery and notifications.
                            </p>
                            <button className="text-sm font-bold text-[#4B2E7E] hover:underline">
                                Update contact →
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {/* Current Account Info Summary */}
            <div className="bg-white rounded-xl border-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)] p-6">
                <h2 className="text-lg font-bold mb-5" style={fontSyne}>Current Account Information</h2>
                
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label className="text-xs font-bold text-[#6E6570] uppercase tracking-wide mb-2 block">Store Name</label>
                        <p className="text-sm font-semibold" style={fontPoppins}>Tolentino Skin Studio</p>
                    </div>
                    
                    <div>
                        <label className="text-xs font-bold text-[#6E6570] uppercase tracking-wide mb-2 block">Store Category</label>
                        <p className="text-sm font-semibold" style={fontPoppins}>Health and Beauty</p>
                    </div>
                    
                    <div>
                        <label className="text-xs font-bold text-[#6E6570] uppercase tracking-wide mb-2 block">Email Address</label>
                        <p className="text-sm font-semibold" style={fontPoppins}>kimzhyrelledd@gmail.com</p>
                    </div>
                    
                    <div>
                        <label className="text-xs font-bold text-[#6E6570] uppercase tracking-wide mb-2 block">Contact Number</label>
                        <p className="text-sm font-semibold" style={fontPoppins}>0917 123 4567</p>
                    </div>
                    
                    <div className="md:col-span-2">
                        <label className="text-xs font-bold text-[#6E6570] uppercase tracking-wide mb-2 block">Pickup Address</label>
                        <p className="text-sm font-semibold" style={fontPoppins}>
                            Purok 4, Rizal Street, Apokon, Tagum City, Davao del Norte 8100
                        </p>
                    </div>
                </div>

                <div className="flex items-center gap-2 mt-6 pt-6 border-t border-[color-mix(in_srgb,#B9A6DE_24%,white)]">
                    <Package className="w-4 h-4 text-[#6E6570]" />
                    <span className="text-xs text-[#6E6570]">
                        Member since: <span className="font-bold">September 2024</span>
                    </span>
                </div>
            </div>
        </SellerShell>
    );
}
