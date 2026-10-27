@extends('layouts.sorting')

@section('title', 'Messages')

@push('styles')
<style>
/* â”€â”€ Layout shell â”€â”€ */
.chat-wrap {
  display: flex;
  height: calc(100vh - 5rem);
  height: calc(100dvh - 4.25rem - var(--app-main-y, 1rem));
  min-height: 480px;
  overflow: hidden;
  border: 1px solid var(--color-border);
  border-radius: 0.75rem;
  background: var(--color-card);
}

/* â”€â”€ Left sidebar â”€â”€ */
.chat-sidebar {
  width: 280px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  border-right: 1px solid var(--color-border);
  background: var(--color-card);
}
.sidebar-top {
  padding: 14px 14px 10px;
  border-bottom: 1px solid var(--color-border);
}
.sidebar-title {
  font-size: 16px;
  font-weight: 800;
  margin-bottom: 10px;
  color: var(--color-foreground);
}
.search-wrap {
  position: relative;
}
.search-wrap svg {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--color-muted-foreground);
  pointer-events: none;
}
.search-wrap input {
  width: 100%;
  border: 1px solid var(--color-border);
  border-radius: 20px;
  padding: 7px 12px 7px 34px;
  font-size: 12.5px;
  font-family: inherit;
  background: var(--color-muted);
  color: var(--color-foreground);
  outline: none;
  box-sizing: border-box;
}
.search-wrap input:focus {
  background: var(--color-background);
  border-color: var(--color-ring);
}
.chat-tabs {
  display: flex;
  gap: 4px;
  padding: 8px 14px;
  border-bottom: 1px solid var(--color-border);
  flex-shrink: 0;
}
.ctab {
  font-size: 12px;
  font-weight: 600;
  padding: 5px 12px;
  border-radius: 20px;
  cursor: pointer;
  border: none;
  background: transparent;
  color: var(--color-muted-foreground);
  font-family: inherit;
  transition: background .15s, color .15s;
}
.ctab.active { background: var(--color-primary); color: var(--color-primary-foreground); }
.ctab:not(.active):hover { background: var(--color-muted); color: var(--color-foreground); }

/* Conversation list */
.convo-list { flex: 1; overflow-y: auto; }
.convo-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  cursor: pointer;
  transition: background .12s;
  position: relative;
}
.convo-item:hover { background: var(--color-muted); }
.convo-item.active { background: var(--color-accent); }
.convo-avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 700;
  color: #fff;
  flex-shrink: 0;
  position: relative;
}
.convo-avatar.rider  { background: var(--color-primary); }
.convo-avatar.seller { background: oklch(0.35 0.12 160); }
.convo-avatar.admin  { background: oklch(0.35 0.10 60); }
.online-ring::after {
  content: '';
  position: absolute;
  bottom: 1px;
  right: 1px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: var(--ok-600,#16a34a);
  border: 2px solid var(--color-card);
}
.convo-body { flex: 1; min-width: 0; }
.convo-top { display: flex; justify-content: space-between; align-items: baseline; gap: 4px; margin-bottom: 2px; }
.convo-name { font-size: 13px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--color-foreground); }
.convo-time { font-size: 11px; color: var(--color-muted-foreground); flex-shrink: 0; }
.convo-preview { font-size: 12px; color: var(--color-muted-foreground); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.convo-preview.unread { color: var(--color-foreground); font-weight: 600; }
.convo-ref { font-size: 10.5px; color: var(--color-primary); font-weight: 600; margin-top: 1px; }
.unread-badge {
  min-width: 18px;
  height: 18px;
  border-radius: 9px;
  background: var(--color-primary);
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
  flex-shrink: 0;
}

/* â”€â”€ Right panel â”€â”€ */
.chat-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
  background: var(--color-background);
}

/* Header */
.chat-panel-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 18px;
  border-bottom: 1px solid var(--color-border);
  background: var(--color-card);
  flex-shrink: 0;
}
.head-who { display: flex; align-items: center; gap: 10px; }
.head-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: var(--color-primary);
  color: var(--color-primary-foreground);
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  position: relative;
}
.head-avatar.online::after {
  content: '';
  position: absolute;
  bottom: 1px;
  right: 1px;
  width: 9px;
  height: 9px;
  border-radius: 50%;
  background: var(--ok-600,#16a34a);
  border: 2px solid var(--color-card);
}
.head-name { font-size: 14px; font-weight: 700; color: var(--color-foreground); line-height: 1.2; }
.head-status { font-size: 11.5px; color: var(--ok-600, #16a34a); font-weight: 600; }
.head-status.offline { color: var(--color-muted-foreground); }
.head-actions { display: flex; align-items: center; gap: 8px; }
.chat-panel-head .chat-back-btn { display: none; }
.head-action-btn {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: none;
  background: var(--color-muted);
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background .15s;
}
.head-action-btn:hover { background: var(--color-accent); }
.input-icon-btn:hover  { background: var(--color-accent); }
.ref-chip {
  font-size: 11px;
  font-weight: 700;
  border: 1.5px solid var(--color-border);
  border-radius: 20px;
  padding: 3px 10px;
  color: var(--color-muted-foreground);
  background: var(--color-muted);
  letter-spacing: .3px;
}

/* Messages area */
.messages-area {
  flex: 1;
  overflow-y: auto;
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.day-label {
  align-self: center;
  font-size: 11px;
  color: var(--color-muted-foreground);
  background: var(--color-card);
  border: 1px solid var(--color-border);
  border-radius: 20px;
  padding: 3px 14px;
  margin: 6px 0 10px;
  backdrop-filter: blur(4px);
}

/* Message groups â€” avatar shows once per group */
.msg-group { display: flex; flex-direction: column; margin-bottom: 12px; }
.msg-group.me { align-items: flex-end; }
.msg-group.them { align-items: flex-start; }

.msg-group-inner { display: flex; align-items: flex-end; gap: 8px; }
.msg-group.me   .msg-group-inner { flex-direction: row-reverse; }
.msg-group.them .msg-group-inner { flex-direction: row; }

.msg-avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: var(--color-primary);
  color: var(--color-primary-foreground);
  font-size: 10px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  align-self: flex-end;
  margin-bottom: 0;
}
.msg-avatar.me-av { background: var(--color-primary); }

.msg-bubbles { display: flex; flex-direction: column; gap: 3px; max-width: 65%; }
.msg-group.me   .msg-bubbles { align-items: flex-end; }
.msg-group.them .msg-bubbles { align-items: flex-start; }

.msg-bubble {
  padding: 9px 14px;
  font-size: 13.5px;
  line-height: 1.45;
  word-break: break-word;
  white-space: pre-wrap;
  width: fit-content;
  max-width: 100%;
}
/* Rounded corners â€” first, middle, last in group */
.msg-group.them .msg-bubble { background: var(--color-muted); color: var(--color-foreground); box-shadow: 0 1px 2px rgba(0,0,0,.07); }
.msg-group.me   .msg-bubble { background: var(--color-primary); color: var(--color-primary-foreground); }

/* Top bubble */
.msg-group.them .msg-bubble:first-child { border-radius: 18px 18px 18px 4px; }
.msg-group.them .msg-bubble:last-child  { border-radius: 4px 18px 18px 18px; }
.msg-group.them .msg-bubble:only-child  { border-radius: 18px 18px 18px 4px; }
.msg-group.them .msg-bubble:not(:first-child):not(:last-child) { border-radius: 4px 18px 18px 4px; }

.msg-group.me .msg-bubble:first-child { border-radius: 18px 18px 4px 18px; }
.msg-group.me .msg-bubble:last-child  { border-radius: 18px 4px 18px 18px; }
.msg-group.me .msg-bubble:only-child  { border-radius: 18px 18px 4px 18px; }
.msg-group.me .msg-bubble:not(:first-child):not(:last-child) { border-radius: 18px 4px 4px 18px; }

.msg-meta {
  display: flex;
  align-items: center;
  gap: 5px;
  margin-top: 3px;
  padding: 0 2px;
}
.msg-group.me   .msg-meta { flex-direction: row-reverse; }
.msg-time { font-size: 11px; color: var(--color-muted-foreground); }
.msg-group.me .msg-time { color: var(--color-muted-foreground); }
.msg-seen {
  font-size: 10px;
  color: var(--color-primary);
  font-weight: 600;
}

/* Input bar */
.chat-input-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  background: var(--color-card);
  border-top: 1px solid var(--color-border);
  flex-shrink: 0;
}
.input-icon-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: none;
  background: var(--color-muted);
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
  transition: background .15s;
}
.input-icon-btn:hover { background: #e0daf4; }
.chat-input-wrap {
  flex: 1;
  display: flex;
  align-items: center;
  background: var(--color-muted);
  border-radius: 22px;
  padding: 0 14px;
  gap: 8px;
  min-height: 40px;
}
.chat-input {
  flex: 1;
  border: none;
  background: transparent;
  padding: 9px 0;
  font-size: 13.5px;
  font-family: inherit;
  color: var(--color-foreground);
  outline: none;
  resize: none;
  max-height: 120px;
  line-height: 1.4;
}
.chat-input::placeholder { color: var(--color-muted-foreground); }
.emoji-btn {
  color: var(--color-muted-foreground);
  background: none;
  border: none;
  cursor: pointer;
  font-size: 18px;
  line-height: 1;
  padding: 0;
  flex-shrink: 0;
  transition: transform .15s;
}
.emoji-btn:hover { transform: scale(1.2); }
.send-btn {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: var(--color-primary);
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
  transition: background .15s, transform .1s;
}
.send-btn:hover { background: var(--color-accent); transform: scale(1.05); }

/* Quick-reply chips */
.quick-reply-bar {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px 8px;
  background: var(--color-card);
  border-top: 1px solid var(--color-border);
  flex-shrink: 0;
  overflow-x: auto;
}
.quick-reply-chip {
  flex-shrink: 0;
  border: 1px solid var(--color-border);
  border-radius: 20px;
  padding: 5px 12px;
  font-size: 12px;
  font-weight: 600;
  font-family: inherit;
  color: var(--color-foreground);
  background: var(--color-muted);
  cursor: pointer;
  transition: background .15s, border-color .15s;
}
.quick-reply-chip:hover {
  background: var(--color-accent);
  border-color: var(--color-primary);
  color: var(--color-primary);
}

/* Tab counts */
.ctab-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 18px;
  height: 18px;
  border-radius: 9px;
  font-size: 10px;
  font-weight: 700;
  padding: 0 5px;
  margin-left: 4px;
  background: var(--color-muted);
  color: var(--color-muted-foreground);
}
.ctab.active .ctab-count {
  background: rgba(255,255,255,.2);
  color: #fff;
}

.dark .head-action-btn,
.dark .input-icon-btn,
.dark .convo-ref,
.dark .msg-seen {
  color: #8fb0ff;
}

@media (max-width: 639px) {
  .chat-wrap {
    display: block;
    min-height: 0;
  }
  .chat-sidebar {
    width: 100%;
    height: 100%;
    border-right: 0;
  }
  .chat-panel {
    height: 100%;
  }
  .chat-wrap.has-active .chat-sidebar,
  .chat-wrap:not(.has-active) .chat-panel {
    display: none;
  }
  .chat-panel-head .chat-back-btn {
    display: flex;
  }
  .chat-panel-head {
    gap: 8px;
    padding: 10px 12px;
  }
  .head-who {
    min-width: 0;
  }
  .head-who > div {
    min-width: 0;
  }
  .head-name {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .head-actions .ref-chip,
  .head-actions .head-action-btn:last-child {
    display: none;
  }
  .messages-area {
    padding: 12px;
  }
  .msg-bubbles {
    max-width: 78%;
  }
  .chat-input-bar {
    gap: 6px;
    padding: 8px 10px;
  }
  .quick-reply-bar {
    padding: 6px 10px 8px;
  }
}
</style>
@endpush

@section('content')
<div style="margin:calc(var(--app-main-y, 1rem) * -1) calc(var(--app-main-x, 1rem) * -1) 0;">
<div class="chat-wrap {{ request('conversation') ? 'has-active' : '' }}">

  {{-- â”€â”€ LEFT SIDEBAR â”€â”€ --}}
  <div class="chat-sidebar">
    <form method="GET" action="{{ route('chat.index') }}" class="sidebar-top">
      <div class="sidebar-title">Messages</div>
      <div class="search-wrap">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search conversations or messages..." aria-label="Search conversations">
      </div>
      <div class="mt-2 flex items-center gap-2">
        <select name="sort" onchange="this.form.submit()" class="w-full rounded-full border border-border bg-muted px-3 py-1.5 text-[12px] text-foreground" aria-label="Sort conversations">
          <option value="">Newest first</option>
          <option value="recent" {{ request('sort') === 'recent' ? 'selected' : '' }}>Recent activity</option>
          <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest first</option>
          <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name A-Z</option>
        </select>
        <button type="submit" class="btn-soft shrink-0 px-3 py-1.5 text-[12px]">Search</button>
      </div>
    </form>

    <div class="chat-tabs">
      <button class="ctab active" onclick="filterTab(this,'all')">All <span class="ctab-count">{{ $tabCounts['all'] }}</span></button>
      <button class="ctab" onclick="filterTab(this,'rider')">Riders <span class="ctab-count">{{ $tabCounts['rider'] }}</span></button>
      <button class="ctab" onclick="filterTab(this,'seller')">Sellers <span class="ctab-count">{{ $tabCounts['seller'] }}</span></button>
      <button class="ctab" onclick="filterTab(this,'admin')">Admin <span class="ctab-count">{{ $tabCounts['admin'] }}</span></button>
    </div>

    <div class="convo-list" id="convoList">
      @forelse ($conversations as $c)
        <div class="convo-item {{ $c['id'] === $activeConvo['id'] ? 'active' : '' }}"
             data-type="{{ $c['type'] }}"
             data-name="{{ strtolower($c['name']) }}"
             data-url="{{ route('chat.index', ['conversation' => $c['id']]) }}"
             onclick="openConvo(this)">
          <div class="convo-avatar {{ $c['type'] }} {{ $c['online'] ? 'online-ring' : '' }}">
            {{ $c['initials'] }}
          </div>
          <div class="convo-body">
            <div class="convo-top">
              <div class="convo-name">{{ $c['name'] }}</div>
              <div class="convo-time">{{ $c['time'] }}</div>
            </div>
            <div class="convo-preview {{ $c['unread'] > 0 ? 'unread' : '' }}">{{ $c['preview'] }}</div>
            {{-- Make ref a link to the parcel --}}
            @if (!empty($c['ref']) && preg_match('/#\d+/', $c['ref'], $m))
              <a href="{{ route('delivery.monitoring', ['q' => $m[0]]) }}" class="convo-ref" onclick="event.stopPropagation()">{{ $c['ref'] }}</a>
            @else
              <div class="convo-ref">{{ $c['ref'] ?? '' }}</div>
            @endif
          </div>
          @if ($c['unread'] > 0)
            <div class="unread-badge">{{ $c['unread'] }}</div>
          @endif
        </div>
      @empty
        <div class="flex flex-col items-center justify-center py-12 text-center" id="noConvos">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--color-muted-foreground); opacity:.4; margin-bottom:8px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <div style="font-size:13px; font-weight:600; color:var(--color-muted-foreground);">No conversations</div>
          <div style="font-size:11.5px; color:var(--color-muted-foreground); opacity:.7; margin-top:2px;">Messages will appear here when riders or sellers contact you</div>
        </div>
      @endforelse
    </div>
  </div>

  {{-- â”€â”€ RIGHT PANEL â”€â”€ --}}
  <div class="chat-panel">

    {{-- Header --}}
    <div class="chat-panel-head">
      <div class="head-who">
        <button type="button" class="head-action-btn chat-back-btn" title="Back to conversations" aria-label="Back to conversations"
                onclick="window.location.href='{{ route('chat.index') }}'">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
        </button>
        <div class="head-avatar {{ $activeConvo['online'] ? 'online' : '' }}">{{ $activeConvo['initials'] }}</div>
        <div>
          <div class="head-name">{{ $activeConvo['name'] }}</div>
          <div class="head-status {{ $activeConvo['online'] ? '' : 'offline' }}">
            {{ $activeConvo['online'] ? 'Active now' : 'Offline' }}
          </div>
        </div>
      </div>
      <div class="head-actions">
        <div class="ref-chip">{{ $activeConvo['ref'] ?? 'GENERAL' }}</div>
        {{-- Call icon --}}
        <button class="head-action-btn" title="Call">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.78a16 16 0 0 0 6.29 6.29l.94-.94a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        </button>
        {{-- Info icon --}}
        <button class="head-action-btn" title="Info">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        </button>
      </div>
    </div>

    {{-- Messages --}}
    <div class="messages-area" id="messagesArea">
      <div class="day-label">Today</div>

      @php
        $grouped = [];
        $prev = null;
        foreach ($messages as $i => $msg) {
          if ($prev === null || $prev['from'] !== $msg['from']) {
            $grouped[] = ['from' => $msg['from'], 'bubbles' => [$msg]];
          } else {
            $grouped[count($grouped)-1]['bubbles'][] = $msg;
          }
          $prev = $msg;
        }
      @endphp

      @if (empty($messages))
        <div class="flex flex-1 flex-col items-center justify-center py-12 text-center">
          <div style="font-size:13px; font-weight:600; color:var(--color-muted-foreground);">No messages yet</div>
          <div style="font-size:11.5px; color:var(--color-muted-foreground); opacity:.7; margin-top:2px;">Send the first message below.</div>
        </div>
      @endif

      @foreach ($grouped as $grp)
        <div class="msg-group {{ $grp['from'] }}">
          <div class="msg-group-inner">
            {{-- Avatar â€” only for "them", shown once per group --}}
            @if ($grp['from'] === 'them')
              <div class="msg-avatar">{{ $activeConvo['initials'] }}</div>
            @endif

            <div class="msg-bubbles">
              @foreach ($grp['bubbles'] as $b)
                <div class="msg-bubble">{{ $b['text'] }}</div>
              @endforeach
            </div>

            @if ($grp['from'] === 'me')
              <div class="msg-avatar me-av">LG</div>
            @endif
          </div>
          {{-- Timestamp below the group --}}
          <div class="msg-meta">
            <span class="msg-time">{{ $grp['bubbles'][array_key_last($grp['bubbles'])]['time'] }}</span>
            @if ($grp['from'] === 'me')
              <span class="msg-seen">Seen</span>
            @endif
          </div>
        </div>
      @endforeach
    </div>

    {{-- Input bar --}}
    <div class="chat-input-bar">
      {{-- Attach --}}
      <button class="input-icon-btn" title="Attach">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
      </button>

      {{-- Text input --}}
      <div class="chat-input-wrap">
        <textarea class="chat-input" id="chatInput" rows="1"
                   placeholder="Message {{ $activeConvo['name'] }}..."
                   onkeydown="handleKey(event)" oninput="autoResize(this)" {{ $activeConvo['id'] ? '' : 'disabled' }}></textarea>
        <button class="emoji-btn" title="Emoji" aria-label="Insert emoji">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><path d="M9 9h.01M15 9h.01"/></svg>
        </button>
      </div>

      {{-- Send --}}
      <button class="send-btn" onclick="sendMessage()" title="Send" {{ $activeConvo['id'] ? '' : 'disabled' }}>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
      </button>
    </div>

    {{-- Quick-reply chips --}}
    <div class="quick-reply-bar">
      @foreach (['On my way', 'Parcel received', 'Delivery confirmed', 'Need to reschedule'] as $chip)
        <button class="quick-reply-chip" onclick="insertQuickReply('{{ $chip }}')">{{ $chip }}</button>
      @endforeach
    </div>

  </div>
</div>
</div>
@endsection

@push('scripts')
<script>
const area = document.getElementById('messagesArea');
area.scrollTop = area.scrollHeight;

function handleKey(e) {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
}

function autoResize(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

const activeConversationId = {{ (int) $activeConvo['id'] }};

function appendMyMessage(text, time) {
  const grp = document.createElement('div');
  grp.className = 'msg-group me';
  grp.innerHTML = `
    <div class="msg-group-inner">
      <div class="msg-bubbles" style="align-items:flex-end;">
        <div class="msg-bubble" style="border-radius:18px 18px 4px 18px;">${escHtml(text)}</div>
      </div>
      <div class="msg-avatar me-av">LG</div>
    </div>
    <div class="msg-meta" style="flex-direction:row-reverse;">
      <span class="msg-seen">Sent</span>
      <span class="msg-time">${escHtml(time)}</span>
    </div>
  `;
  area.appendChild(grp);
  area.scrollTop = area.scrollHeight;
}

function sendMessage() {
  const input = document.getElementById('chatInput');
  const text = input.value.trim();
  if (!text || !activeConversationId) return;

  const now = new Date().toLocaleTimeString('en-US', { hour:'numeric', minute:'2-digit' });

  fetch(`/chat/${activeConversationId}/messages`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
      'Accept': 'application/json',
    },
    body: JSON.stringify({ body: text }),
  })
    .then(async response => {
      const data = await response.json();
      if (!response.ok) throw new Error(data.message || 'Unable to send message.');
      return data;
    })
    .then(data => {
      appendMyMessage(text, data.data?.time || now);
      input.value = '';
      input.style.height = 'auto';
      const empty = area.querySelector('.flex-1');
      if (empty) empty.remove();
    })
    .catch(error => {
      if (window.showToast) window.showToast(error.message, { type: 'error' });
      else alert(error.message);
    });
}

function escHtml(s) {
  return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function filterTab(btn, type) {
  document.querySelectorAll('.ctab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.convo-item').forEach(el => {
    el.style.display = (type === 'all' || el.dataset.type === type) ? '' : 'none';
  });
}

function searchConvos(val) {
  const q = val.toLowerCase();
  document.querySelectorAll('.convo-item').forEach(el => {
    el.style.display = el.dataset.name.includes(q) ? '' : 'none';
  });
}


function openConvo(el) {
  if (!el.dataset.url) return;
  window.location.href = el.dataset.url;
}

function insertQuickReply(text) {
  const input = document.getElementById('chatInput');
  input.value = text;
  input.focus();
  autoResize(input);
}
</script>
@endpush
