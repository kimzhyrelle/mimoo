import { Head } from '@inertiajs/react';
import SellerShell from '@/layouts/seller/seller-shell';
import { MessageSquare, Search, Send, Paperclip, MoreVertical } from 'lucide-react';

export default function SellerMessages() {
    return (
        <>
            <Head title="Messages - Seller Dashboard" />
            <SellerShell active="messages">
                <div className="mx-auto max-w-[1400px]">
                    {/* Header */}
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-gray-900" style={{ fontFamily: "'Syne', sans-serif" }}>
                            Messages
                        </h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Communicate with your customers
                        </p>
                    </div>

                    {/* Messages Container */}
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Conversations List */}
                        <div className="lg:col-span-1 rounded-lg border border-gray-200 bg-white">
                            <div className="border-b border-gray-200 p-4">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" strokeWidth={1.5} />
                                    <input
                                        type="text"
                                        placeholder="Search conversations..."
                                        className="w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-4 text-sm focus:border-[#3B1F6B] focus:outline-none focus:ring-2 focus:ring-[#3B1F6B]/20"
                                    />
                                </div>
                            </div>
                            <div className="h-[600px] overflow-y-auto">
                                {/* Empty State */}
                                <div className="flex h-full items-center justify-center p-6">
                                    <div className="text-center">
                                        <MessageSquare className="mx-auto h-12 w-12 text-gray-300" strokeWidth={1.5} />
                                        <p className="mt-2 text-sm font-medium text-gray-500">No conversations yet</p>
                                        <p className="text-xs text-gray-400">Customer messages will appear here</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Chat Area */}
                        <div className="lg:col-span-2 rounded-lg border border-gray-200 bg-white">
                            {/* Empty State - No Conversation Selected */}
                            <div className="flex h-[688px] items-center justify-center p-6">
                                <div className="text-center">
                                    <div className="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gray-100">
                                        <MessageSquare className="h-10 w-10 text-gray-400" strokeWidth={1.5} />
                                    </div>
                                    <p className="mt-4 text-lg font-semibold text-gray-900">No conversation selected</p>
                                    <p className="mt-1 text-sm text-gray-500">
                                        Select a conversation from the list to start chatting
                                    </p>
                                </div>
                            </div>

                            {/* Chat Header (Hidden when no conversation) */}
                            <div className="hidden border-b border-gray-200 p-4">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <div className="flex h-10 w-10 items-center justify-center rounded-full bg-[#3B1F6B] text-sm font-bold text-white">
                                            JD
                                        </div>
                                        <div>
                                            <p className="text-sm font-semibold text-gray-900">John Doe</p>
                                            <p className="text-xs text-gray-500">Active now</p>
                                        </div>
                                    </div>
                                    <button className="rounded-lg p-2 hover:bg-gray-100 transition">
                                        <MoreVertical className="h-5 w-5 text-gray-500" strokeWidth={1.5} />
                                    </button>
                                </div>
                            </div>

                            {/* Messages Area (Hidden when no conversation) */}
                            <div className="hidden h-[512px] overflow-y-auto p-4">
                                {/* Messages will go here */}
                            </div>

                            {/* Message Input (Hidden when no conversation) */}
                            <div className="hidden border-t border-gray-200 p-4">
                                <div className="flex items-center gap-2">
                                    <button className="rounded-lg p-2 hover:bg-gray-100 transition">
                                        <Paperclip className="h-5 w-5 text-gray-500" strokeWidth={1.5} />
                                    </button>
                                    <input
                                        type="text"
                                        placeholder="Type a message..."
                                        className="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm focus:border-[#3B1F6B] focus:outline-none focus:ring-2 focus:ring-[#3B1F6B]/20"
                                    />
                                    <button className="rounded-lg bg-[#3B1F6B] p-2 text-white hover:bg-[#2D1652] transition">
                                        <Send className="h-5 w-5" strokeWidth={1.5} />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </SellerShell>
        </>
    );
}
