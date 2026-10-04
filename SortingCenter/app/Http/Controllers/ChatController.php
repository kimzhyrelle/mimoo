<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        // Static conversations — replace with DB queries when the chat backend is built
        $conversations = [
            [
                'id'       => 1,
                'type'     => 'rider',
                'initials' => 'R1',
                'name'     => 'Rider 01 · J. Ramos',
                'preview'  => 'Tanginamo daw sabi ni tan',
                'ref'      => 'RE: #1004',
                'time'     => '9:41 AM',
                'online'   => true,
                'unread'   => 0,
            ],
            [
                'id'       => 2,
                'type'     => 'seller',
                'initials' => 'RD',
                'name'     => 'Rosales Dry Goods',
                'preview'  => 'Parcel is packed and ready for pickup',
                'ref'      => 'RE: #1007',
                'time'     => '8:55 AM',
                'online'   => false,
                'unread'   => 2,
            ],
            [
                'id'       => 3,
                'type'     => 'rider',
                'initials' => 'R2',
                'name'     => 'Rider 02 · M. Cruz',
                'preview'  => 'Hindi ko na-deliver yung parcel, wal...',
                'ref'      => 'RE: #0997',
                'time'     => '8:12 AM',
                'online'   => false,
                'unread'   => 0,
            ],
            [
                'id'       => 4,
                'type'     => 'admin',
                'initials' => 'AD',
                'name'     => 'Admin — Platform Ops',
                'preview'  => 'Reminder: submit weekly report by F...',
                'ref'      => 'GENERAL',
                'time'     => 'Yesterday',
                'online'   => false,
                'unread'   => 1,
            ],
            [
                'id'       => 5,
                'type'     => 'seller',
                'initials' => 'LP',
                'name'     => 'Lakeview Produce',
                'preview'  => 'Can we reschedule pickup to 1 PM?',
                'ref'      => 'RE: #1009',
                'time'     => 'Mon',
                'online'   => false,
                'unread'   => 0,
            ],
        ];

        // Active conversation messages (first convo pre-loaded)
        $activeConvo = $conversations[0];
        $messages = [
            ['from' => 'them', 'text' => 'Sir, ready na po ba yung parcel sa Poblacion I?', 'time' => '9:38 AM'],
            ['from' => 'me',   'text' => 'Opo, nasa Bay 2 na. Pwede na kunin.',             'time' => '9:39 AM'],
            ['from' => 'them', 'text' => 'Salamat po, papasok na ako.',                      'time' => '9:41 AM'],
            ['from' => 'me',   'text' => 'Tanginamo daw sabi ni tan',                        'time' => 'Just now'],
        ];

        $activeTab = $request->get('tab', 'all');

        return view('sorting.chat', compact('conversations', 'activeConvo', 'messages', 'activeTab'));
    }
}
