<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XSVLX11F7H"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-XSVLX11F7H');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}" async defer></script>
</head>

<body data-base-url="{{ url('/') }}">
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light shadow-sm">
            <div class="container">
                <div class="row">
                    <span class="navbar-brand">
                        <img src="{{ url('/images/Studiomeds_cropped.png') }}" class="logo" width='250'>
                    </span>
                </div>
                <br>
                <div class="row">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <!-- Left Side Of Navbar -->
                        <ul class="navbar-nav me-auto">

                        </ul>
                        <!-- Right Side Of Navbar -->
                        <ul class="navbar-nav ms-auto">
                            <!-- Authentication Links -->
                            @auth
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('/dashboard') }}">{{ __('My Dashboard') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('/logout') }}">{{ __('Log Out') }}</a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <main class="py-4">

            @if ($errors->any())
            <div class="container-fluid">
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            @if(Session::has('message'))
            <div class="container-fluid message">
                <div class="text-center alert @if (Session::has('type')) alert-{{ session('type') }} @elseif(isset($type) && $type != '')alert-{{ $type }} @else alert-secondary @endif">{{ session('message') }}</div>
            </div>
            @elseif(isset($message) && $message != '')
            <div class="container-fluid message">
                <div class="text-center alert @if(isset($type) && $type != '')alert-{{ $type }} @else alert-secondary @endif">{{ $message }}</div>
            </div>
            @endif
            <div class="container-fluid">
                @yield('content')
            </div>
        </main>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" language="javascript" src="//cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/fontawesome.min.js" integrity="sha512-KCwrxBJebca0PPOaHELfqGtqkUlFUCuqCnmtydvBSTnJrBirJ55hRG5xcP4R9Rdx9Fz9IF3Yw6Rx40uhuAHR8Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.0/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script>
        $('.select2').select2({
            theme: 'bootstrap-5',
        });
    </script>
    <script>
    // ── UTM capture (non-blocking) ───────────────────────────────────────
    // Reads utm_source/medium/campaign from URL on every page load,
    // persists to cookie (90-day) + localStorage, and pings the server.
    (function() {
        try {
            function uuid() {
                if (window.crypto && typeof crypto.randomUUID === 'function') {
                    return crypto.randomUUID();
                }
                return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                    var r = Math.random() * 16 | 0;
                    var v = c === 'x' ? r : (r & 0x3 | 0x8);
                    return v.toString(16);
                });
            }
            function setCookie(name, value, days) {
                try {
                    var d = new Date();
                    d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
                    document.cookie = name + '=' + encodeURIComponent(value) +
                        ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
                } catch (e) {}
            }
            function getCookie(name) {
                try {
                    var match = document.cookie.match('(?:^|; )' + name + '=([^;]*)');
                    return match ? decodeURIComponent(match[1]) : null;
                } catch (e) { return null; }
            }
            function lsGet(k) { try { return localStorage.getItem(k); } catch (e) { return null; } }
            function lsSet(k, v) { try { localStorage.setItem(k, v); } catch (e) {} }

            var sessionId = lsGet('sm_utm_session_id');
            if (!sessionId) {
                sessionId = uuid();
                lsSet('sm_utm_session_id', sessionId);
            }
            window.__smUtmSessionId = sessionId;

            var params = new URLSearchParams(window.location.search);
            var fromUrl = {
                utm_source:   params.get('utm_source'),
                utm_medium:   params.get('utm_medium'),
                utm_campaign: params.get('utm_campaign'),
            };
            var hasAny = fromUrl.utm_source || fromUrl.utm_medium || fromUrl.utm_campaign;

            var stored = null;
            try {
                var raw = lsGet('utm_data') || getCookie('utm_data');
                if (raw) { stored = JSON.parse(raw); }
            } catch (e) { stored = null; }

            var data = stored || {};
            if (hasAny) {
                if (fromUrl.utm_source)   data.utm_source   = fromUrl.utm_source;
                if (fromUrl.utm_medium)   data.utm_medium   = fromUrl.utm_medium;
                if (fromUrl.utm_campaign) data.utm_campaign = fromUrl.utm_campaign;
                var json = JSON.stringify(data);
                setCookie('utm_data', json, 90);
                lsSet('utm_data', json);
            }

            var payload = {
                session_id:   sessionId,
                utm_source:   data.utm_source   || null,
                utm_medium:   data.utm_medium   || null,
                utm_campaign: data.utm_campaign || null,
            };

            // Fire & forget; never block page load.
            setTimeout(function() {
                try {
                    var fd = new FormData();
                    Object.keys(payload).forEach(function(k) {
                        if (payload[k] !== null) { fd.append(k, payload[k]); }
                    });
                    fetch('/ajax/track-utm-visit', {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin',
                        keepalive: true,
                    }).catch(function() {});
                } catch (e) {}
            }, 0);

            // Populate the hidden field on the patient registration form when present
            document.addEventListener('DOMContentLoaded', function() {
                var hidden = document.getElementById('utm_session_id');
                if (hidden) { hidden.value = sessionId; }
            });
        } catch (e) {
            // Silently fail — UTM capture must never break the page.
        }
    })();
    </script>
    @yield('script')
</body>

</html>