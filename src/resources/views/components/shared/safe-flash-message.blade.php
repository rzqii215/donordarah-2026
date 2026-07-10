@php
    $safeFlashKey = 'donordarah.safe_flash_message_rendered';

    $safeFlashAlreadyRendered = app()->bound($safeFlashKey)
        && app($safeFlashKey) === true;

    if (! $safeFlashAlreadyRendered) {
        app()->instance($safeFlashKey, true);
    }

    $safeFlashMessages = [];

    if (! $safeFlashAlreadyRendered) {
        foreach ([
            'success' => 'Berhasil',
            'error' => 'Akses Ditolak',
            'warning' => 'Perhatian',
            'info' => 'Informasi',
            'status' => 'Informasi',
        ] as $safeFlashType => $safeFlashTitle) {
            $safeFlashValue = session($safeFlashType);

            if (blank($safeFlashValue)) {
                continue;
            }

            $safeFlashMessages[] = [
                'type' => $safeFlashType === 'status' ? 'info' : $safeFlashType,
                'title' => $safeFlashTitle,
                'message' => is_array($safeFlashValue)
                    ? implode(' ', $safeFlashValue)
                    : (string) $safeFlashValue,
            ];
        }
    }
@endphp

@if (! $safeFlashAlreadyRendered && count($safeFlashMessages) > 0)
    <div class="safe-flash-stack" data-safe-flash-stack>
        @foreach ($safeFlashMessages as $safeFlash)
            <div class="safe-flash-message safe-flash-{{ $safeFlash['type'] }}" data-safe-flash-message>
                <div class="safe-flash-icon">
                    @if ($safeFlash['type'] === 'success')
                        ✓
                    @elseif ($safeFlash['type'] === 'error')
                        !
                    @elseif ($safeFlash['type'] === 'warning')
                        !
                    @else
                        i
                    @endif
                </div>

                <div class="safe-flash-content">
                    <strong>{{ $safeFlash['title'] }}</strong>
                    <p>{{ $safeFlash['message'] }}</p>
                </div>

                <button
                    type="button"
                    class="safe-flash-close"
                    data-safe-flash-close
                    aria-label="Tutup pesan"
                >
                    &times;
                </button>
            </div>
        @endforeach
    </div>
@endif

@if (! $safeFlashAlreadyRendered)
    <style>
        .safe-flash-stack {
            position: fixed;
            top: 22px;
            right: 22px;
            z-index: 99999;
            width: min(420px, calc(100vw - 32px));
            display: grid;
            gap: 12px;
            pointer-events: none;
        }

        .safe-flash-message {
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr) 34px;
            gap: 12px;
            align-items: start;
            padding: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 22px 60px rgba(15, 23, 42, 0.16);
            pointer-events: auto;
            animation: safeFlashIn 180ms ease both;
        }

        .safe-flash-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 15px;
            font-size: 18px;
            font-weight: 1000;
        }

        .safe-flash-content strong {
            display: block;
            color: #0f172a;
            font-size: 13px;
            font-weight: 1000;
            line-height: 1.2;
        }

        .safe-flash-content p {
            margin: 5px 0 0;
            color: #475569;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.55;
        }

        .safe-flash-close {
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 12px;
            color: #64748b;
            background: #f8fafc;
            font-size: 22px;
            line-height: 1;
            cursor: pointer;
        }

        .safe-flash-success {
            border-color: #bbf7d0;
        }

        .safe-flash-success .safe-flash-icon {
            color: #15803d;
            background: #dcfce7;
        }

        .safe-flash-error {
            border-color: #fecaca;
        }

        .safe-flash-error .safe-flash-icon {
            color: #dc2626;
            background: #fee2e2;
        }

        .safe-flash-warning {
            border-color: #fed7aa;
        }

        .safe-flash-warning .safe-flash-icon {
            color: #ea580c;
            background: #ffedd5;
        }

        .safe-flash-info {
            border-color: #bfdbfe;
        }

        .safe-flash-info .safe-flash-icon {
            color: #2563eb;
            background: #dbeafe;
        }

        @keyframes safeFlashIn {
            from {
                opacity: 0;
                transform: translateY(-8px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 640px) {
            .safe-flash-stack {
                top: 14px;
                right: 14px;
                left: 14px;
                width: auto;
            }

            .safe-flash-message {
                grid-template-columns: 38px minmax(0, 1fr) 32px;
                border-radius: 18px;
            }

            .safe-flash-icon {
                width: 38px;
                height: 38px;
            }
        }
    </style>

    <script>
        document.addEventListener('click', function (event) {
            const closeButton = event.target.closest('[data-safe-flash-close]');

            if (!closeButton) {
                return;
            }

            const message = closeButton.closest('[data-safe-flash-message]');

            if (message) {
                message.remove();
            }
        });

        window.setTimeout(function () {
            document.querySelectorAll('[data-safe-flash-message]').forEach(function (message) {
                message.remove();
            });
        }, 5200);
    </script>
@endif
