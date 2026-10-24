import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { BuyerHeader } from '@/components/buyer-header';
import { User, Lock, MapPin, Bell, CreditCard, Shield } from 'lucide-react';

interface BuyerAccProps {
  auth: {
    user: {
      id: number;
      name: string;
      email: string;
      first_name: string;
      last_name: string;
      contact_number?: string;
      birthday?: string;
      sex?: string;
      account_type: string;
    };
  };
}

export default function BuyerAcc({ auth }: BuyerAccProps) {
  const user = auth.user;
  const [activeTab, setActiveTab] = useState<'profile' | 'security' | 'addresses' | 'notifications' | 'payment'>('profile');
  const [cartCount, setCartCount] = useState(0);

  function handleLogout() {
    router.post('/logout');
  }

  return (
    <div className="min-h-screen bg-neutral-50 font-[Syne]">
      <Head title="Account Settings - mimoo" />

      <BuyerHeader
        user={user}
        onLogout={handleLogout}
        cartCount={cartCount}
      />

      <main className="mx-auto max-w-[1200px] px-8 py-8">
        {/* Page Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-neutral-900">Account Settings</h1>
          <p className="text-neutral-600 mt-2">Manage your account information and preferences</p>
        </div>

        <div className="grid grid-cols-12 gap-6">
          {/* Sidebar Navigation */}
          <div className="col-span-3">
            <div className="rounded-xl border border-neutral-200 bg-white p-4">
              <nav className="space-y-1">
                <button
                  onClick={() => setActiveTab('profile')}
                  className={`flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition ${
                    activeTab === 'profile'
                      ? 'bg-violet-50 text-violet-700'
                      : 'text-neutral-700 hover:bg-neutral-50'
                  }`}
                >
                  <User className="h-5 w-5" />
                  Profile Information
                </button>

                <button
                  onClick={() => setActiveTab('security')}
                  className={`flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition ${
                    activeTab === 'security'
                      ? 'bg-violet-50 text-violet-700'
                      : 'text-neutral-700 hover:bg-neutral-50'
                  }`}
                >
                  <Lock className="h-5 w-5" />
                  Security
                </button>

                <button
                  onClick={() => setActiveTab('addresses')}
                  className={`flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition ${
                    activeTab === 'addresses'
                      ? 'bg-violet-50 text-violet-700'
                      : 'text-neutral-700 hover:bg-neutral-50'
                  }`}
                >
                  <MapPin className="h-5 w-5" />
                  Addresses
                </button>

                <button
                  onClick={() => setActiveTab('notifications')}
                  className={`flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition ${
                    activeTab === 'notifications'
                      ? 'bg-violet-50 text-violet-700'
                      : 'text-neutral-700 hover:bg-neutral-50'
                  }`}
                >
                  <Bell className="h-5 w-5" />
                  Notifications
                </button>

                <button
                  onClick={() => setActiveTab('payment')}
                  className={`flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition ${
                    activeTab === 'payment'
                      ? 'bg-violet-50 text-violet-700'
                      : 'text-neutral-700 hover:bg-neutral-50'
                  }`}
                >
                  <CreditCard className="h-5 w-5" />
                  Payment Methods
                </button>
              </nav>
            </div>
          </div>

          {/* Content Area */}
          <div className="col-span-9">
            <div className="rounded-xl border border-neutral-200 bg-white p-8">
              {activeTab === 'profile' && (
                <div>
                  <h2 className="text-xl font-bold text-neutral-900 mb-6">Profile Information</h2>
                  
                  <div className="space-y-6">
                    {/* Profile Photo */}
                    <div>
                      <label className="block text-sm font-medium text-neutral-700 mb-2">
                        Profile Photo
                      </label>
                      <div className="flex items-center gap-4">
                        <div className="h-20 w-20 rounded-full bg-violet-100 flex items-center justify-center text-2xl font-bold text-violet-700">
                          {user.first_name?.[0]}{user.last_name?.[0]}
                        </div>
                        <button className="rounded-lg border border-neutral-300 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 transition">
                          Change Photo
                        </button>
                      </div>
                    </div>

                    {/* Name Fields */}
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <label className="block text-sm font-medium text-neutral-700 mb-2">
                          First Name
                        </label>
                        <input
                          type="text"
                          defaultValue={user.first_name}
                          className="w-full rounded-lg border border-neutral-300 px-4 py-2 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20"
                        />
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-neutral-700 mb-2">
                          Last Name
                        </label>
                        <input
                          type="text"
                          defaultValue={user.last_name}
                          className="w-full rounded-lg border border-neutral-300 px-4 py-2 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20"
                        />
                      </div>
                    </div>

                    {/* Email */}
                    <div>
                      <label className="block text-sm font-medium text-neutral-700 mb-2">
                        Email Address
                      </label>
                      <input
                        type="email"
                        defaultValue={user.email}
                        className="w-full rounded-lg border border-neutral-300 px-4 py-2 bg-neutral-50"
                        disabled
                      />
                      <p className="text-xs text-neutral-500 mt-1">Email cannot be changed</p>
                    </div>

                    {/* Contact Number */}
                    <div>
                      <label className="block text-sm font-medium text-neutral-700 mb-2">
                        Contact Number
                      </label>
                      <input
                        type="tel"
                        defaultValue={user.contact_number}
                        placeholder="+63 XXX XXX XXXX"
                        className="w-full rounded-lg border border-neutral-300 px-4 py-2 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20"
                      />
                    </div>

                    {/* Birthday */}
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <label className="block text-sm font-medium text-neutral-700 mb-2">
                          Birthday
                        </label>
                        <input
                          type="date"
                          defaultValue={user.birthday}
                          className="w-full rounded-lg border border-neutral-300 px-4 py-2 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20"
                        />
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-neutral-700 mb-2">
                          Gender
                        </label>
                        <select
                          defaultValue={user.sex}
                          className="w-full rounded-lg border border-neutral-300 px-4 py-2 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20"
                        >
                          <option value="">Select Gender</option>
                          <option value="Male">Male</option>
                          <option value="Female">Female</option>
                          <option value="Other">Prefer not to say</option>
                        </select>
                      </div>
                    </div>

                    {/* Save Button */}
                    <div className="flex justify-end pt-4">
                      <button className="rounded-lg bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-violet-700 transition">
                        Save Changes
                      </button>
                    </div>
                  </div>
                </div>
              )}

              {activeTab === 'security' && (
                <div>
                  <h2 className="text-xl font-bold text-neutral-900 mb-6">Security Settings</h2>
                  
                  <div className="space-y-6">
                    {/* Change Password */}
                    <div>
                      <h3 className="text-sm font-semibold text-neutral-900 mb-4">Change Password</h3>
                      <div className="space-y-4">
                        <div>
                          <label className="block text-sm font-medium text-neutral-700 mb-2">
                            Current Password
                          </label>
                          <input
                            type="password"
                            className="w-full rounded-lg border border-neutral-300 px-4 py-2 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20"
                          />
                        </div>
                        <div>
                          <label className="block text-sm font-medium text-neutral-700 mb-2">
                            New Password
                          </label>
                          <input
                            type="password"
                            className="w-full rounded-lg border border-neutral-300 px-4 py-2 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20"
                          />
                        </div>
                        <div>
                          <label className="block text-sm font-medium text-neutral-700 mb-2">
                            Confirm New Password
                          </label>
                          <input
                            type="password"
                            className="w-full rounded-lg border border-neutral-300 px-4 py-2 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20"
                          />
                        </div>
                      </div>
                    </div>

                    {/* Two-Factor Authentication */}
                    <div className="pt-6 border-t border-neutral-200">
                      <div className="flex items-center justify-between">
                        <div>
                          <h3 className="text-sm font-semibold text-neutral-900 flex items-center gap-2">
                            <Shield className="h-5 w-5 text-violet-600" />
                            Two-Factor Authentication
                          </h3>
                          <p className="text-sm text-neutral-600 mt-1">
                            Add an extra layer of security to your account
                          </p>
                        </div>
                        <button className="rounded-lg border border-violet-600 bg-white px-4 py-2 text-sm font-semibold text-violet-600 hover:bg-violet-50 transition">
                          Enable 2FA
                        </button>
                      </div>
                    </div>

                    {/* Save Button */}
                    <div className="flex justify-end pt-4">
                      <button className="rounded-lg bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-violet-700 transition">
                        Update Password
                      </button>
                    </div>
                  </div>
                </div>
              )}

              {activeTab === 'addresses' && (
                <div>
                  <h2 className="text-xl font-bold text-neutral-900 mb-6">Saved Addresses</h2>
                  
                  <div className="space-y-4">
                    <div className="rounded-lg border border-neutral-200 p-4">
                      <p className="text-sm text-neutral-600">No saved addresses yet.</p>
                    </div>
                    
                    <button className="rounded-lg border border-violet-600 bg-white px-4 py-2 text-sm font-semibold text-violet-600 hover:bg-violet-50 transition">
                      + Add New Address
                    </button>
                  </div>
                </div>
              )}

              {activeTab === 'notifications' && (
                <div>
                  <h2 className="text-xl font-bold text-neutral-900 mb-6">Notification Preferences</h2>
                  
                  <div className="space-y-4">
                    {/* Email Notifications */}
                    <div className="flex items-center justify-between py-4 border-b border-neutral-200">
                      <div>
                        <h3 className="text-sm font-semibold text-neutral-900">Order Updates</h3>
                        <p className="text-sm text-neutral-600">Receive notifications about your orders</p>
                      </div>
                      <label className="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" className="sr-only peer" defaultChecked />
                        <div className="w-11 h-6 bg-neutral-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-violet-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                      </label>
                    </div>

                    <div className="flex items-center justify-between py-4 border-b border-neutral-200">
                      <div>
                        <h3 className="text-sm font-semibold text-neutral-900">Promotions & Deals</h3>
                        <p className="text-sm text-neutral-600">Get notified about special offers</p>
                      </div>
                      <label className="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" className="sr-only peer" />
                        <div className="w-11 h-6 bg-neutral-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-violet-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                      </label>
                    </div>

                    <div className="flex items-center justify-between py-4">
                      <div>
                        <h3 className="text-sm font-semibold text-neutral-900">New Products</h3>
                        <p className="text-sm text-neutral-600">Stay updated with new arrivals</p>
                      </div>
                      <label className="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" className="sr-only peer" defaultChecked />
                        <div className="w-11 h-6 bg-neutral-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-violet-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                      </label>
                    </div>
                  </div>
                </div>
              )}

              {activeTab === 'payment' && (
                <div>
                  <h2 className="text-xl font-bold text-neutral-900 mb-6">Payment Methods</h2>
                  
                  <div className="space-y-4">
                    <div className="rounded-lg border border-neutral-200 p-4">
                      <p className="text-sm text-neutral-600">No saved payment methods yet.</p>
                    </div>
                    
                    <button className="rounded-lg border border-violet-600 bg-white px-4 py-2 text-sm font-semibold text-violet-600 hover:bg-violet-50 transition">
                      + Add Payment Method
                    </button>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}
