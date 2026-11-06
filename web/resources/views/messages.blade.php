<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages — Mimoo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@400;700&family=Syne:wght@600;700;800&family=Poppins:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Brand Color Palette — "Midnight Meadow" (exact hex) */
            --midnight: #2A1B4D;
            --plum: #4B2E7E;
            --plum-hover: color-mix(in srgb, var(--plum) 84%, black);
            --lavender: #B9A6DE;
            --stone: #6E6570;
            --white: #ffffff;
            --lav-50: color-mix(in srgb, var(--lavender) 14%, white);
            --lav-100: color-mix(in srgb, var(--lavender) 28%, white);
            --lav-200: color-mix(in srgb, var(--lavender) 48%, white);
            --ok: #2c7a52;
            --radius-sm: 10px;
            --radius-md: 14px;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        body {
            background: var(--lav-50);
            color: var(--midnight);
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        .num, time {
            font-family: 'Poppins', sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button {
            font-family: inherit;
            cursor: pointer;
        }

        input, textarea {
            font-family: inherit;
        }

        svg {
            display: block;
        }

        /* ---------- Top bar ---------- */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 40px;
            background: var(--midnight);
            flex-shrink: 0;
        }

        .brand {
            font-family: 'Syncopate', sans-serif;
            font-weight: 700;
            font-size: 19px;
            letter-spacing: 0.14em;
            color: #fff;
        }

        .brand span {
            color: var(--lavender);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 26px;
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.68);
        }

        .topbar-right a {
            padding-bottom: 3px;
            border-bottom: 2px solid transparent;
            font-weight: 600;
        }

        .topbar-right a:hover {
            color: #fff;
        }

        .topbar-right a.active {
            color: #fff;
            border-bottom-color: var(--lavender);
        }

        /* ---------- Body layout ---------- */
        .msg-app {
            flex: 1;
            display: flex;
            min-height: 0;
            max-width: 1240px;
            width: 100%;
            margin: 0 auto;
        }

        /* ---------- Conversation list ---------- */
        .conv-panel {
            width: 340px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            background: var(--lav-50);
            border-right: 1px solid var(--lav-200);
            min-height: 0;
        }

        .conv-head {
            padding: 22px 20px 14px;
            flex-shrink: 0;
        }

        h1.panel-title {
            font-family: 'Syncopate', sans-serif;
            font-weight: 700;
            font-size: 16px;
            letter-spacing: 0.1em;
            color: var(--midnight);
            margin: 0 0 14px;
        }

        .search-row {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--white);
            border: 1px solid var(--lav-200);
            border-radius: var(--radius-sm);
            padding: 9px 12px;
        }

        .search-row svg {
            flex-shrink: 0;
            color: var(--stone);
        }

        .search-row input {
            border: none;
            outline: none;
            flex: 1;
            font-size: 13px;
            color: var(--midnight);
            background: transparent;
        }

        .search-row input::placeholder {
            color: var(--stone);
        }

        .conv-tabs {
            display: flex;
            gap: 20px;
            padding: 14px 20px 0;
            border-bottom: 1px solid var(--lav-200);
            flex-shrink: 0;
        }

        .conv-tab {
            background: none;
            border: none;
            font-size: 12.5px;
            font-weight: 700;
            color: var(--stone);
            padding: 0 0 11px;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
        }

        .conv-tab.active {
            color: var(--midnight);
            border-bottom-color: var(--plum);
        }

        .conv-tab .n {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 10.5px;
            margin-left: 4px;
            color: var(--plum);
        }

        .conv-list {
            overflow-y: auto;
            flex: 1;
        }

        .conv-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 20px;
            cursor: pointer;
            position: relative;
            border-radius: var(--radius-sm);
            margin: 2px 8px;
        }

        .conv-item:hover {
            background: var(--lav-100);
        }

        .conv-item.active {
            background: var(--white);
            box-shadow: inset 3px 0 0 var(--plum);
        }

        .conv-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
            background: var(--plum);
            flex-shrink: 0;
            position: relative;
        }

        .online-mark {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--ok);
            border: 2px solid var(--lav-50);
        }

        .conv-item.active .online-mark,
        .conv-item:hover .online-mark {
            border-color: var(--white);
        }

        .conv-body {
            flex: 1;
            min-width: 0;
        }

        .conv-top-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 8px;
        }

        .conv-name {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--midnight);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conv-time {
            font-size: 11px;
            color: var(--stone);
            flex-shrink: 0;
        }

        .conv-preview {
            font-size: 12.5px;
            color: var(--stone);
            margin-top: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 210px;
        }

        .conv-item.unread .conv-preview {
            color: var(--midnight);
            font-weight: 600;
        }

        .conv-item.unread .conv-name {
            color: var(--plum);
        }

        .unread-badge {
            min-width: 18px;
            height: 18px;
            padding: 0 6px;
            border-radius: 99px;
            background: var(--plum);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 10px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 2px;
        }

        /* ---------- Chat panel ---------- */
        .chat-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
            min-width: 0;
            background: var(--lav-100);
        }

        .chat-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 26px;
            background: var(--white);
            border-bottom: 1px solid var(--lav-200);
            flex-shrink: 0;
        }

        .chat-head-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .chat-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--plum);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            font-size: 11px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .chat-title {
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: var(--midnight);
        }

        .chat-sub {
            font-size: 12px;
            color: var(--stone);
            margin-top: 2px;
        }

        .chat-sub.online {
            color: var(--ok);
            font-weight: 600;
        }

        .chat-head-actions {
            display: flex;
            gap: 16px;
            color: var(--stone);
        }

        .chat-head-actions svg:hover {
            color: var(--plum);
            cursor: pointer;
        }

        .order-ref {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin: 14px 26px 0;
            padding: 11px 15px;
            border-radius: var(--radius-md);
            background: var(--lav-200);
            flex-shrink: 0;
        }

        .order-ref-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .order-ref .thumb {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            font-size: 10px;
            font-weight: 700;
            color: var(--plum);
            flex-shrink: 0;
            background: var(--white);
        }

        .order-ref-text {
            min-width: 0;
        }

        .order-ref-id {
            font-size: 11px;
            color: var(--plum);
            font-weight: 600;
        }

        .order-ref-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--midnight);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .order-ref-status {
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--midnight);
            background: var(--white);
            padding: .3rem .6rem;
            border-radius: 99px;
            flex-shrink: 0;
        }

        .order-ref a.view-link {
            font-size: 12px;
            font-weight: 700;
            color: var(--midnight);
            flex-shrink: 0;
        }

        .order-ref a.view-link:hover {
            text-decoration: underline;
        }

        .thread {
            flex: 1;
            overflow-y: auto;
            padding: 22px 26px;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .date-sep {
            text-align: center;
            font-size: 11px;
            color: var(--stone);
            font-weight: 600;
            margin: 16px 0 12px;
        }

        .sys-note {
            align-self: center;
            font-size: 11px;
            color: var(--stone);
            background: var(--white);
            border-radius: 99px;
            padding: 6px 14px;
            margin: 8px 0;
        }

        .bubble-row {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            margin: 4px 0;
            max-width: 74%;
            position: relative;
        }

        .bubble-row.in {
            align-self: flex-start;
        }

        .bubble-row.out {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .bubble-avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-family: 'Poppins', sans-serif;
            font-size: 9px;
            font-weight: 700;
            color: #fff;
        }

        .bubble-row.in .bubble-avatar {
            background: var(--plum);
        }

        .bubble-row.out .bubble-avatar {
            background: var(--midnight);
        }

        .bubble-stack {
            min-width: 0;
        }

        .bubble {
            font-family: 'Inter', sans-serif;
            padding: 11px 15px;
            font-size: 13.5px;
            line-height: 1.5;
            position: relative;
        }

        .bubble-row.in .bubble {
            background: var(--white);
            color: var(--midnight);
            border-radius: 4px 16px 16px 16px;
        }

        .bubble-row.out .bubble {
            background: var(--plum);
            color: #fff;
            border-radius: 16px 4px 16px 16px;
        }

        .quote-block {
            font-size: 11.5px;
            opacity: .85;
            border-left: 2px solid currentColor;
            padding: 2px 0 2px 8px;
            margin-bottom: 6px;
        }

        .bubble-meta {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            color: var(--stone);
            margin: 3px 34px 0;
        }

        .bubble-row.out + .bubble-meta {
            align-self: flex-end;
            margin: 3px 0 0;
            justify-content: flex-end;
        }

        .bubble-meta svg {
            width: 13px;
            height: 13px;
        }

        .tick-seen {
            color: var(--plum);
        }

        .tick-delivered, .tick-sent {
            color: var(--stone);
        }

        .typing-row {
            align-self: flex-start;
            margin: 6px 0 0 34px;
        }

        .typing-bubble {
            background: var(--white);
            border-radius: 4px 16px 16px 16px;
            padding: 10px 14px;
            display: inline-flex;
            gap: 4px;
        }

        .typing-bubble span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--stone);
            animation: pulse 1.2s infinite ease-in-out;
        }

        .typing-bubble span:nth-child(2) {
            animation-delay: .15s;
        }

        .typing-bubble span:nth-child(3) {
            animation-delay: .3s;
        }

        @keyframes pulse {
            0%, 60%, 100% {
                opacity: .3;
                transform: translateY(0);
            }
            30% {
                opacity: 1;
                transform: translateY(-2px);
            }
        }

        .reply-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: none;
            background: var(--lav-200);
            color: var(--plum);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity .15s;
        }

        .bubble-row.in .reply-btn {
            right: -34px;
        }

        .bubble-row.out .reply-btn {
            left: -34px;
        }

        .bubble-row:hover .reply-btn {
            opacity: 1;
            pointer-events: auto;
        }

        .reply-btn:hover {
            background: var(--plum);
            color: #fff;
        }

        .reply-btn svg {
            width: 13px;
            height: 13px;
        }

        .reply-preview {
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin: 0 26px;
            padding: 9px 13px;
            background: var(--lav-200);
            border-left: 3px solid var(--plum);
            border-radius: var(--radius-sm);
            flex-shrink: 0;
        }

        .reply-preview.open {
            display: flex;
        }

        .reply-preview .rp-text {
            min-width: 0;
        }

        .reply-preview .rp-lbl {
            font-size: 11px;
            font-weight: 700;
            color: var(--plum);
        }

        .reply-preview .rp-snip {
            font-size: 12px;
            color: var(--stone);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .reply-preview button {
            appearance: none;
            border: none;
            background: none;
            color: var(--stone);
            flex-shrink: 0;
            display: flex;
        }

        .reply-preview button:hover {
            color: var(--midnight);
        }

        .reply-preview button svg {
            width: 16px;
            height: 16px;
        }

        .composer {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 26px;
            background: var(--white);
            border-top: 1px solid var(--lav-200);
            flex-shrink: 0;
        }

        .composer-icons {
            display: flex;
            gap: 12px;
            flex-shrink: 0;
            color: var(--stone);
        }

        .composer-icons svg {
            width: 18px;
            height: 18px;
        }

        .composer-icons svg:hover {
            color: var(--plum);
            cursor: pointer;
        }

        .composer input {
            flex: 1;
            border: 1px solid var(--lav-200);
            background: var(--lav-50);
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            font-size: 13.5px;
            outline: none;
            color: var(--midnight);
        }

        .composer input:focus {
            border-color: var(--plum);
        }

        .composer input::placeholder {
            color: var(--stone);
        }

        .send-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--midnight);
            color: #fff;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .send-btn svg {
            width: 16px;
            height: 16px;
        }

        .send-btn:hover {
            background: var(--plum-hover);
        }

        @media (max-width: 860px) {
            .conv-panel {
                width: 280px;
            }
            .topbar {
                padding: 14px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="brand">MIMOO<span>.</span></div>
        <div class="topbar-right">
            <a href="/">Shop</a>
            <a href="/orders">My Orders</a>
            <a href="/messages" class="active">Messages</a>
        </div>
    </div>

    <div class="msg-app">
        <!-- Conversation list -->
        <div class="conv-panel">
            <div class="conv-head">
                <h1 class="panel-title">MESSAGES</h1>
                <div class="search-row">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" placeholder="Search sellers or orders">
                </div>
            </div>
            <div class="conv-tabs">
                <button class="conv-tab active" data-filter="all">All<span class="n">6</span></button>
                <button class="conv-tab" data-filter="unread">Unread<span class="n">3</span></button>
            </div>
            <div class="conv-list" id="convList"></div>
        </div>

        <!-- Chat panel -->
        <div class="chat-panel">
            <div class="chat-head">
                <div class="chat-head-left">
                    <div class="chat-avatar" id="chatAvatar">NA</div>
                    <div>
                        <div class="chat-title" id="chatTitle">Northlight Audio</div>
                        <div class="chat-sub" id="chatSub">Active now</div>
                    </div>
                </div>
                <div class="chat-head-actions">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="1"/>
                        <circle cx="19" cy="12" r="1"/>
                        <circle cx="5" cy="12" r="1"/>
                    </svg>
                </div>
            </div>

            <div class="order-ref" id="orderRef"></div>
            <div class="thread" id="thread"></div>

            <div class="reply-preview" id="replyPreview">
                <div class="rp-text">
                    <div class="rp-lbl" id="rpLabel">Replying to Northlight Audio</div>
                    <div class="rp-snip" id="rpSnippet">Message text…</div>
                </div>
                <button type="button" id="rpCancel" aria-label="Cancel reply">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            <div class="composer">
                <div class="composer-icons">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/>
                    </svg>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                        <line x1="9" y1="9" x2="9.01" y2="9"/>
                        <line x1="15" y1="9" x2="15.01" y2="9"/>
                    </svg>
                </div>
                <input type="text" id="messageInput" placeholder="Message this seller…">
                <button class="send-btn" id="sendBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <script>
const conversations = [
    {
        id: "northlight",
        name: "Northlight Audio",
        initials: "NA",
        online: true,
        unread: 2,
        lastTime: "4 min",
        lastPreview: "Your earbuds will ship out tomorrow morning!",
        order: { id: "MM-2026-10432", item: "Wireless Earbuds — Pro Fit", status: "To Ship", thumb: "WE" },
        messages: [
            { type: "sys", text: "You started a conversation about Order MM-2026-10432" },
            { date: "Today" },
            { from: "in", text: "Hi Aira! Thanks for your order. Just confirming — do you want the charcoal or white earbuds?", time: "3:40 PM" },
            { from: "out", text: "Charcoal please! That's what I selected at checkout.", time: "3:42 PM", status: "seen" },
            { from: "in", text: "Perfect, got it. We're packing it up now.", time: "3:43 PM" },
            { from: "in", text: "Your earbuds will ship out tomorrow morning!", time: "3:44 PM" },
            { typing: true }
        ]
    },
    {
        id: "keycraft",
        name: "Keycraft Studio",
        initials: "KS",
        online: false,
        unread: 0,
        lastTime: "33 min",
        lastPreview: "Awesome, thank you for the update!",
        order: { id: "MM-2026-10298", item: "Mechanical Keyboard — 75%", status: "In Transit", thumb: "MK" },
        messages: [
            { type: "sys", text: "You started a conversation about Order MM-2026-10298" },
            { date: "Yesterday" },
            { from: "out", text: "Hi! Just wondering when my keyboard order will ship?", time: "10:58 AM", status: "seen" },
            { from: "in", text: "Hey Aira, it shipped out yesterday afternoon via J&T Express.", time: "11:20 AM" },
            { from: "in", text: "It's on the J&T truck now, should arrive by the 19th.", time: "11:21 AM" },
            { from: "out", text: "Awesome, thank you for the update!", time: "11:25 AM", status: "delivered" }
        ]
    },
    {
        id: "nordic",
        name: "Nordic Home Co.",
        initials: "NH",
        online: true,
        unread: 1,
        lastTime: "2 h",
        lastPreview: "Got it, thanks for letting me know!",
        order: { id: "MM-2026-10201", item: "Ceramic Pour-Over Set", status: "Out for Delivery", thumb: "CP" },
        messages: [
            { type: "sys", text: "You started a conversation about Order MM-2026-10201" },
            { date: "Today" },
            { from: "in", text: "Good morning! Your pour-over set left our hub early today.", time: "8:10 AM" },
            { from: "in", text: "Rider is out for delivery, you should get it this afternoon.", time: "8:32 AM" },
            { from: "out", text: "Got it, thanks for letting me know!", time: "8:35 AM", status: "sent" }
        ]
    },
    {
        id: "woven",
        name: "Woven & Co.",
        initials: "WC",
        online: false,
        unread: 0,
        lastTime: "3 d",
        lastPreview: "Glad it arrived safely! Let us know if you have questions.",
        order: { id: "MM-2026-09877", item: "Canvas Tote — Structured", status: "Delivered", thumb: "CT" },
        messages: [
            { type: "sys", text: "You started a conversation about Order MM-2026-09877" },
            { date: "Sep 1" },
            { from: "out", text: "Just received the totes, they look great!", time: "2:10 PM", status: "seen" },
            { from: "in", text: "Glad it arrived safely! Let us know if you have questions.", time: "2:15 PM" }
        ]
    },
    {
        id: "claybound",
        name: "Claybound Ceramics",
        initials: "CC",
        online: false,
        unread: 0,
        lastTime: "Aug 16",
        lastPreview: "Thank you so much for the 5-star review!",
        order: { id: "MM-2026-09340", item: "Stoneware Mug Set of 4", status: "Rated", thumb: "SM" },
        messages: [
            { type: "sys", text: "You started a conversation about Order MM-2026-09340" },
            { date: "Aug 16" },
            { from: "in", text: "Thank you so much for the 5-star review!", time: "9:02 AM" },
            { from: "in", text: "Hope you enjoy the mugs, come back anytime.", time: "9:03 AM" }
        ]
    },
    {
        id: "lumen",
        name: "Lumen Living",
        initials: "LL",
        online: false,
        unread: 0,
        lastTime: "Aug 5",
        lastPreview: "Refund has been sent to your GCash wallet.",
        order: { id: "MM-2026-09112", item: "Table Lamp — Arc", status: "Cancelled", thumb: "TL" },
        messages: [
            { type: "sys", text: "You started a conversation about Order MM-2026-09112" },
            { date: "Aug 5" },
            { from: "in", text: "We've processed your cancellation request.", time: "9:50 AM" },
            { from: "in", text: "Refund has been sent to your GCash wallet.", time: "10:02 AM" }
        ]
    }
];

let activeId = "northlight";
let replyTarget = null;

function tickIcon(status) {
    if (status === 'sent') {
        return `<svg class="tick-sent" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Sent</span>`;
    }
    if (status === 'delivered') {
        return `<svg class="tick-delivered" viewBox="0 0 24 24" fill="none"><path d="m2 13 4 4 4-4M9 13l4 4 8-9" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Delivered</span>`;
    }
    if (status === 'seen') {
        return `<svg class="tick-seen" viewBox="0 0 24 24" fill="none"><path d="m2 13 4 4 4-4M9 13l4 4 8-9" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Seen</span>`;
    }
    return '';
}

function renderConvList(filter) {
    const listEl = document.getElementById('convList');
    const items = conversations.filter(c => filter === 'unread' ? c.unread > 0 : true);
    listEl.innerHTML = items.map(c => `
        <div class="conv-item ${c.unread > 0 ? 'unread' : ''} ${c.id === activeId ? 'active' : ''}" data-id="${c.id}">
            <div class="conv-avatar">${c.initials}${c.online ? '<span class="online-mark"></span>' : ''}</div>
            <div class="conv-body">
                <div class="conv-top-row">
                    <span class="conv-name">${c.name}</span>
                    <span class="conv-time num">${c.lastTime}</span>
                </div>
                <div class="conv-preview">${c.lastPreview}</div>
            </div>
            ${c.unread > 0 ? `<span class="unread-badge">${c.unread}</span>` : ''}
        </div>
    `).join('');

    listEl.querySelectorAll('.conv-item').forEach(el => {
        el.addEventListener('click', () => {
            activeId = el.dataset.id;
            const c = conversations.find(x => x.id === activeId);
            c.unread = 0;
            replyTarget = null;
            renderConvList(document.querySelector('.conv-tab.active').dataset.filter);
            renderChat(c);
        });
    });
}

function closeReply() {
    replyTarget = null;
    document.getElementById('replyPreview').classList.remove('open');
}

function openReply(name, text) {
    replyTarget = { name, text };
    document.getElementById('rpLabel').textContent = `Replying to ${name}`;
    document.getElementById('rpSnippet').textContent = text;
    document.getElementById('replyPreview').classList.add('open');
    document.getElementById('messageInput').focus();
}

function renderChat(c) {
    document.getElementById('chatTitle').textContent = c.name;
    document.getElementById('chatAvatar').textContent = c.initials;
    const subEl = document.getElementById('chatSub');
    subEl.textContent = c.online ? 'Active now' : 'Offline';
    subEl.classList.toggle('online', c.online);

    document.getElementById('orderRef').innerHTML = `
        <div class="order-ref-left">
            <div class="thumb">${c.order.thumb}</div>
            <div class="order-ref-text">
                <div class="order-ref-id">Order <span class="num">${c.order.id}</span></div>
                <div class="order-ref-name">${c.order.item}</div>
            </div>
        </div>
        <span class="order-ref-status">${c.order.status}</span>
        <a href="/orders" class="view-link">View Order</a>
    `;

    const threadEl = document.getElementById('thread');
    let html = '';
    c.messages.forEach((m, idx) => {
        if (m.type === 'sys') {
            html += `<div class="sys-note">${m.text}</div>`;
        } else if (m.date) {
            html += `<div class="date-sep">${m.date}</div>`;
        } else if (m.typing) {
            html += `<div class="typing-row"><div class="typing-bubble"><span></span><span></span><span></span></div></div>`;
        } else {
            const who = m.from === 'in' ? c.name : 'You';
            html += `
                <div class="bubble-row ${m.from}" data-idx="${idx}">
                    <div class="bubble-avatar">${m.from === 'in' ? c.initials : 'AD'}</div>
                    <div class="bubble-stack">
                        <div class="bubble">${m.text}</div>
                    </div>
                    <button type="button" class="reply-btn" data-idx="${idx}" data-who="${who}" aria-label="Reply">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M9 10 4 15l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M4 15h9a7 7 0 0 0 7-7V6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div class="bubble-meta">${m.time ? `<span class="num">${m.time}</span>` : ''}${m.from === 'out' && m.status ? tickIcon(m.status) : ''}</div>
            `;
        }
    });

    threadEl.innerHTML = html;
    threadEl.scrollTop = threadEl.scrollHeight;
    closeReply();

    threadEl.querySelectorAll('.reply-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const idx = +btn.dataset.idx;
            const msg = c.messages[idx];
            openReply(btn.dataset.who, msg.text);
        });
    });
}

function sendMessage() {
    const input = document.getElementById('messageInput');
    const text = input.value.trim();
    if (!text) return;

    const c = conversations.find(x => x.id === activeId);
    const quoted = replyTarget ? `<div class="quote-block">${replyTarget.name}: ${replyTarget.text}</div>` : '';
    c.messages.push({ from: 'out', text: quoted + text, time: 'Just now', status: 'sent' });
    input.value = '';
    closeReply();
    renderChat(c);
}

document.getElementById('sendBtn').addEventListener('click', sendMessage);
document.getElementById('messageInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') sendMessage();
});
document.getElementById('rpCancel').addEventListener('click', closeReply);

document.querySelectorAll('.conv-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.conv-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        renderConvList(tab.dataset.filter);
    });
});

renderConvList('all');
renderChat(conversations.find(c => c.id === activeId));
    </script>
</body>
</html>
