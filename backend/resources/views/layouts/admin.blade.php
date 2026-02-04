<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sushi Burger Experience Admin')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    @yield('styles')
    <style>
        /* Fix para scroll vertical */
        html, body {
            height: 100%;
            overflow: hidden;
        }

        body.d-flex {
            height: 100vh;
        }

        .main-wrapper {
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .main-wrapper main {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            min-height: 0;
            /* Padding para evitar que la barra del navegador móvil tape contenido */
            padding-bottom: max(80px, env(safe-area-inset-bottom, 0px) + 60px);
        }

        /* Soporte para safe-area en mobile (iOS principalmente) */
        @supports (padding: max(0px)) {
            .main-wrapper main {
                padding-bottom: max(80px, env(safe-area-inset-bottom, 0px) + 60px);
            }
        }

        /* Padding adicional en mobile para evitar que la barra del navegador tape contenido */
        @media (max-width: 768px) {
            .main-wrapper main {
                padding-bottom: max(100px, env(safe-area-inset-bottom, 0px) + 100px) !important;
            }
        }

        /* Estilos personalizados para la scrollbar */
        /* Webkit (Chrome, Safari, Edge) */
        .main-wrapper main::-webkit-scrollbar {
            width: 8px;
        }

        .main-wrapper main::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }

        .main-wrapper main::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.6) 0%, rgba(168, 85, 247, 0.6) 100%);
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .main-wrapper main::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.8) 0%, rgba(168, 85, 247, 0.8) 100%);
            background-clip: padding-box;
        }

        /* Firefox */
        .main-wrapper main {
            scrollbar-width: thin;
            scrollbar-color: rgba(124, 58, 237, 0.6) rgba(0, 0, 0, 0.2);
        }

        /* Aplicar también a otros elementos con scroll */
        *::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        *::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }

        *::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.6) 0%, rgba(168, 85, 247, 0.6) 100%);
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        *::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.8) 0%, rgba(168, 85, 247, 0.8) 100%);
            background-clip: padding-box;
        }

        .swal2-popup-large {
            width: 600px !important;
            max-width: 90vw;
            font-size: 1.1rem;
        }
        .swal2-title-large {
            font-size: 1.75rem !important;
            font-weight: 700 !important;
            margin-bottom: 1.5rem !important;
        }
        .swal2-html-large {
            font-size: 1.1rem;
            line-height: 1.6;
        }
        .swal2-popup-large .swal2-html-container {
            padding: 0 1.5rem !important;
        }
        .swal2-popup-large .swal2-actions {
            margin-top: 2rem !important;
            padding: 0 1.5rem 1.5rem !important;
            gap: 1rem !important;
        }
        .swal2-popup-large .swal2-confirm {
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%) !important;
            border: none !important;
            border-radius: 9999px !important;
            padding: 0.75rem 2rem !important;
            font-weight: 600 !important;
            color: white !important;
            transition: all 0.2s ease !important;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3) !important;
        }
        .swal2-popup-large .swal2-confirm:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.4) !important;
        }
        .swal2-popup-large .swal2-cancel {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 9999px !important;
            padding: 0.75rem 2rem !important;
            font-weight: 600 !important;
            color: var(--color-text) !important;
            transition: all 0.2s ease !important;
        }
        .swal2-popup-large .swal2-cancel:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
        }

        /* Custom Tooltip Styles */
        .custom-tooltip {
            position: absolute;
            z-index: 10000;
            pointer-events: none;
            opacity: 0;
            transform: translateX(-10px);
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .custom-tooltip.show {
            opacity: 1;
            transform: translateX(0);
        }

        .custom-tooltip-content {
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(12px);
            color: white;
            padding: 0.625rem 1rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
            display: flex;
            align-items: center;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4), 0 4px 8px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .custom-tooltip-arrow {
            position: absolute;
            left: -6px;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-top: 6px solid transparent;
            border-bottom: 6px solid transparent;
            border-right: 6px solid rgba(0, 0, 0, 0.85);
            filter: drop-shadow(-2px 0 2px rgba(0, 0, 0, 0.3));
        }
    </style>
</head>
<body class="d-flex overflow-hidden">
    @php
        $name = Auth::user()->name;
        $initials = collect(explode(' ', $name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('');
    @endphp

    <!-- Overlay para mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="logo">Sushi Burger<span class="dot">.</span></a>
            <button class="sidebar-toggle-btn" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
                <i data-lucide="panel-left-close" style="width: 18px; height: 18px;"></i>
            </button>
        </div>

        <nav class="sidebar-nav flex-grow-1 mt-4">
            <a href="{{ route('admin.dashboard') }}" class="nav-item-cartify {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.restaurants') }}" class="nav-item-cartify {{ request()->routeIs('admin.restaurants') ? 'active' : '' }}">
                <i data-lucide="building-2"></i>
                <span>Restaurantes</span>
            </a>
            <a href="{{ route('admin.menu') }}" class="nav-item-cartify {{ request()->routeIs('admin.menu') ? 'active' : '' }}">
                <i data-lucide="utensils-crossed"></i>
                <span>Menú</span>
            </a>
            @php
                $user = Auth::user();
                $company = $user->company;
                $hasEcommerce = $company && $company->hasEcommerce();
                $isFreePlan = $company && $company->isOnFreePlan();
                $hasRestaurants = $company && $company->restaurants()->count() > 0;
            @endphp

            @if($hasEcommerce)
            <a href="{{ route('admin.orders.index') }}" class="nav-item-cartify {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <i data-lucide="shopping-cart"></i>
                <span>Pedidos</span>
            </a>
            <a href="{{ route('admin.mercadopago.index') }}" class="nav-item-cartify {{ request()->routeIs('admin.mercadopago.*') ? 'active' : '' }}">
                <i data-lucide="credit-card"></i>
                <span>MercadoPago</span>
            </a>
            <a href="{{ route('admin.bank-accounts') }}" class="nav-item-cartify {{ request()->routeIs('admin.bank-accounts') ? 'active' : '' }}">
                <i data-lucide="landmark"></i>
                <span>Cuentas Bancarias</span>
            </a>
            @endif
            <a href="{{ route('admin.qrs') }}" class="nav-item-cartify {{ request()->routeIs('admin.qrs') ? 'active' : '' }}">
                <i data-lucide="qr-code"></i>
                <span>Códigos QR</span>
            </a>
            <a href="{{ route('admin.users') }}" class="nav-item-cartify {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                <i data-lucide="users"></i>
                <span>Usuarios</span>
            </a>
            <a href="{{ route('admin.settings') }}" class="nav-item-cartify {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                <i data-lucide="settings"></i>
                <span>Configuración</span>
            </a>

            @if($isFreePlan)
            <div class="nav-item-cartify disabled-premium" style="opacity: 0.5; cursor: not-allowed; position: relative;" title="Exclusivo Plan Full">
                <i data-lucide="palette"></i>
                <span>Personalizar</span>
                <i data-lucide="sparkles" class="premium-icon" style="width: 14px; height: 14px; margin-left: auto; color: var(--color-primary-light);" title="Exclusivo Plan Full"></i>
            </div>
            @elseif(!$hasRestaurants)
            <div class="nav-item-cartify disabled-no-restaurant" style="opacity: 0.5; cursor: not-allowed; position: relative;" title="Debes crear tu primer restaurant">
                <i data-lucide="palette"></i>
                <span>Personalizar</span>
                <i data-lucide="alert-circle" class="no-restaurant-icon" style="width: 14px; height: 14px; margin-left: auto; color: var(--color-text-muted);" title="Debes crear tu primer restaurant"></i>
            </div>
            @else
            <a href="{{ route('admin.personalize') }}" class="nav-item-cartify {{ request()->routeIs('admin.personalize') ? 'active' : '' }}">
                <i data-lucide="palette"></i>
                <span>Personalizar</span>
            </a>
            @endif


            @if(Auth::user()->super_admin)
            <div class="mt-4 mb-2 px-3 text-muted text-uppercase small" style="font-size: 0.7rem; letter-spacing: 0.05em;">Super Admin</div>
            <a href="{{ route('super_admin.dashboard') }}" class="nav-item-cartify {{ request()->routeIs('super_admin.*') ? 'active' : '' }}">
                <i data-lucide="shield-alert"></i>
                <span>Administración</span>
            </a>
            @endif
        </nav>

        <div class="p-3 border-top" style="border-color: var(--color-border) !important;">
            <!--
            <a href="{{ route('admin.subscription') }}" class="nav-item-cartify mb-2 {{ request()->routeIs('admin.subscription') ? 'active' : '' }}">
                <i data-lucide="sparkles"></i>
                <span>Mejorar Plan</span>
            </a>
            -->
            <a href="https://wa.me/59899807750" target="_blank" class="nav-item-cartify mb-2">
                <i data-lucide="message-circle"></i>
                <span>Soporte</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item-cartify w-100 bg-transparent border-0 mb-0">
                    <i data-lucide="log-out"></i>
                    <span>Cerrar sesión</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-grow-1 d-flex flex-column main-wrapper">
        @php
            $user = Auth::user();
            $company = $user->company;
            $isFreePlan = $company && $company->isOnFreePlan();

            // Calcular límites y estados
            $subscriptionAlert = null;
            if ($isFreePlan && $company) {
                $restaurantLimit = $company->getRestaurantLimit();
                $restaurantCount = $company->getRestaurantsCount();
                $userLimit = $company->getUserLimit();
                $userCount = $company->getUsersCount();
                $qrLimit = $company->getQrCodeLimit();
                $qrCount = $company->getTotalQrCodesCount();

                // Detectar si está en el límite o cerca (>= 80%)
                $threshold = 0.8; // 80%

                $alerts = [];

                // Restaurantes
                if ($restaurantLimit !== null) {
                    $percentage = $restaurantCount / $restaurantLimit;
                    if ($restaurantCount >= $restaurantLimit) {
                        $alerts[] = [
                            'type' => 'restaurants',
                            'status' => 'limit_reached',
                            'current' => $restaurantCount,
                            'limit' => $restaurantLimit,
                            'message' => "Has alcanzado el límite de restaurantes ({$restaurantCount}/{$restaurantLimit})"
                        ];
                    } elseif ($percentage >= $threshold) {
                        $remaining = $restaurantLimit - $restaurantCount;
                        $alerts[] = [
                            'type' => 'restaurants',
                            'status' => 'near_limit',
                            'current' => $restaurantCount,
                            'limit' => $restaurantLimit,
                            'remaining' => $remaining,
                            'message' => "Estás cerca del límite de restaurantes ({$restaurantCount}/{$restaurantLimit}, {$remaining} restantes)"
                        ];
                    }
                }

                // Usuarios
                if ($userLimit !== null) {
                    $percentage = $userCount / $userLimit;
                    if ($userCount >= $userLimit) {
                        $alerts[] = [
                            'type' => 'users',
                            'status' => 'limit_reached',
                            'current' => $userCount,
                            'limit' => $userLimit,
                            'message' => "Has alcanzado el límite de usuarios ({$userCount}/{$userLimit})"
                        ];
                    } elseif ($percentage >= $threshold) {
                        $remaining = $userLimit - $userCount;
                        $alerts[] = [
                            'type' => 'users',
                            'status' => 'near_limit',
                            'current' => $userCount,
                            'limit' => $userLimit,
                            'remaining' => $remaining,
                            'message' => "Estás cerca del límite de usuarios ({$userCount}/{$userLimit}, {$remaining} restantes)"
                        ];
                    }
                }

                // QR Codes
                if ($qrLimit !== null) {
                    $percentage = $qrCount / $qrLimit;
                    if ($qrCount >= $qrLimit) {
                        $alerts[] = [
                            'type' => 'qr_codes',
                            'status' => 'limit_reached',
                            'current' => $qrCount,
                            'limit' => $qrLimit,
                            'message' => "Has alcanzado el límite de códigos QR ({$qrCount}/{$qrLimit})"
                        ];
                    } elseif ($percentage >= $threshold) {
                        $remaining = $qrLimit - $qrCount;
                        $alerts[] = [
                            'type' => 'qr_codes',
                            'status' => 'near_limit',
                            'current' => $qrCount,
                            'limit' => $qrLimit,
                            'remaining' => $remaining,
                            'message' => "Estás cerca del límite de códigos QR ({$qrCount}/{$qrLimit}, {$remaining} restantes)"
                        ];
                    }
                }

                // Priorizar alertas: límite alcanzado primero
                usort($alerts, function($a, $b) {
                    if ($a['status'] === 'limit_reached' && $b['status'] !== 'limit_reached') {
                        return -1;
                    }
                    if ($a['status'] !== 'limit_reached' && $b['status'] === 'limit_reached') {
                        return 1;
                    }
                    return 0;
                });

                $subscriptionAlert = !empty($alerts) ? $alerts[0] : null;
            }
        @endphp

        @if($subscriptionAlert)
        <div class="subscription-limit-alert d-flex align-items-center justify-content-between px-4 py-3" style="background: linear-gradient(135deg, rgba(251, 191, 36, 0.15) 0%, rgba(239, 68, 68, 0.15) 100%); border-bottom: 1px solid rgba(251, 191, 36, 0.3);">
            <div class="d-flex align-items-center gap-3">
                <i data-lucide="{{ $subscriptionAlert['status'] === 'limit_reached' ? 'alert-triangle' : 'info' }}" style="width: 20px; height: 20px; color: {{ $subscriptionAlert['status'] === 'limit_reached' ? '#ef4444' : '#fbbf24' }};"></i>
                <div>
                    <strong style="color: white; font-size: 0.9rem;">{{ $subscriptionAlert['message'] }}</strong>
                    <p class="mb-0 small text-muted" style="font-size: 0.8rem;">Actualiza a Plan Full para obtener acceso ilimitado</p>
                </div>
            </div>
            <a href="{{ route('admin.subscription') }}" class="btn btn-sm" style="background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%); border: none; color: white; font-weight: 600; padding: 0.5rem 1.5rem; border-radius: 9999px; white-space: nowrap;">
                Mejorar Plan
            </a>
        </div>
        @endif

        <header class="sbe-navbar cartify-navbar d-flex align-items-center justify-content-between px-4">
            <!-- Botón hamburguesa para mobile -->
            <button class="mobile-menu-toggle" id="mobileMenuToggle" type="button" aria-label="Toggle menu">
                <i data-lucide="menu" style="width: 24px; height: 24px;"></i>
            </button>
            <div class="d-flex align-items-center gap-2">
                @php
                    $user = Auth::user();
                    $company = $user->company;
                    $restaurants = $company ? $company->restaurants : collect();
                    $activeId = session('active_restaurant_id');
                    $activeRestaurant = $restaurants->firstWhere('id', $activeId) ?? $restaurants->first();
                @endphp

                @if($restaurants->count() > 0)
                <div class="dropdown">
                    <button class="btn btn-cartify-secondary dropdown-toggle d-flex align-items-center gap-2 py-2 px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i data-lucide="building" style="width: 16px;"></i>
                        <span class="fw-bold small">{{ $activeRestaurant->name ?? 'Seleccionar Local' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark glass-card border-0 shadow-lg mt-2 p-2">
                        @foreach($restaurants as $res)
                        <li>
                            <form action="{{ route('admin.restaurants.switch') }}" method="POST">
                                @csrf
                                <input type="hidden" name="restaurant_id" value="{{ $res->id }}">
                                <button type="submit" class="dropdown-item rounded-2 d-flex align-items-center justify-content-between py-2 {{ $res->id == ($activeRestaurant->id ?? 0) ? 'active' : '' }}">
                                    <span>{{ $res->name }}</span>
                                    @if($res->id == ($activeRestaurant->id ?? 0))
                                    <i data-lucide="check" style="width: 14px;"></i>
                                    @endif
                                </button>
                            </form>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>

            <div class="d-flex align-items-center gap-4">
                <!--
                <button class="icon-btn">
                    <i data-lucide="search"></i>
                </button>
                <button class="icon-btn position-relative">
                    <i data-lucide="bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="width: 8px; height: 8px;"></span>
                </button>
                -->
                <div class="profile-circle" title="{{ $name }}">
                    {{ strtoupper($initials) }}
                </div>
            </div>
        </header>

        <main class="flex-grow-1 p-4" style="background: radial-gradient(circle at top right, rgba(124, 58, 237, 0.03), transparent 40%);">
            <div class="mb-4">
                <h1 class="page-title fw-bold">@yield('page_title')</h1>
            </div>
            @yield('content')
        </main>
    </div>

    <script>
        lucide.createIcons();

        // Initialize custom tooltips for premium features and disabled items
        document.addEventListener('DOMContentLoaded', function() {
            const premiumElements = document.querySelectorAll('.disabled-premium, .premium-icon, .disabled-no-restaurant, .no-restaurant-icon');
            premiumElements.forEach(function(element) {
                let tooltip = null;

                element.addEventListener('mouseenter', function(e) {
                    const title = this.getAttribute('title');
                    if (title && !tooltip) {
                        tooltip = document.createElement('div');
                        tooltip.className = 'custom-tooltip';
                        tooltip.innerHTML = `
                            <div class="custom-tooltip-content">
                                <i data-lucide="sparkles" style="width: 14px; height: 14px; margin-right: 0.5rem; color: white;"></i>
                                <span>${title}</span>
                            </div>
                            <div class="custom-tooltip-arrow"></div>
                        `;

                        // Add tooltip to body for better positioning
                        document.body.appendChild(tooltip);
                        lucide.createIcons();

                        // Position tooltip
                        const rect = this.getBoundingClientRect();
                        const tooltipRect = tooltip.getBoundingClientRect();
                        const scrollY = window.scrollY || window.pageYOffset;
                        const scrollX = window.scrollX || window.pageXOffset;

                        // Position to the right of the element
                        tooltip.style.top = (rect.top + scrollY + (rect.height / 2) - (tooltipRect.height / 2)) + 'px';
                        tooltip.style.left = (rect.right + scrollX + 12) + 'px';

                        // Show with animation
                        setTimeout(() => {
                            tooltip.classList.add('show');
                        }, 10);
                    }
                });

                element.addEventListener('mouseleave', function() {
                    if (tooltip) {
                        tooltip.classList.remove('show');
                        setTimeout(() => {
                            if (tooltip && tooltip.parentNode) {
                                tooltip.parentNode.removeChild(tooltip);
                            }
                            tooltip = null;
                        }, 200);
                    }
                });
            });
        });

        // Sidebar Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            const mobileToggleBtn = document.getElementById('mobileMenuToggle');
            const overlay = document.getElementById('sidebarOverlay');

            if (!sidebar) {
                console.error('Sidebar not found');
                return;
            }

            // Detectar si estamos en mobile
            const isMobile = () => window.innerWidth <= 991.98;
            let currentIsMobile = isMobile();

            // Función para cerrar sidebar en mobile
            function closeMobileSidebar() {
                if (isMobile()) {
                    sidebar.classList.remove('mobile-open');
                    if (overlay) overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }

            // Función para abrir sidebar en mobile
            function openMobileSidebar() {
                if (isMobile()) {
                    sidebar.classList.add('mobile-open');
                    if (overlay) overlay.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            }

            // Función para actualizar el ícono del toggle desktop
            function updateToggleIcon(shouldCollapse) {
                if (!toggleBtn || isMobile()) return;

                let iconElement = toggleBtn.querySelector('i[data-lucide]');
                if (!iconElement) {
                    iconElement = toggleBtn.querySelector('svg');
                }
                if (iconElement) {
                    iconElement.setAttribute('data-lucide', shouldCollapse ? 'panel-left-open' : 'panel-left-close');
                    lucide.createIcons();
                }
            }

            // Función para actualizar el estado del sidebar (solo desktop)
            function updateSidebarState(shouldCollapse) {
                if (isMobile()) {
                    // En mobile, siempre cerrado (overlay)
                    closeMobileSidebar();
                    return;
                }

                if (shouldCollapse) {
                    sidebar.classList.add('collapsed');
                    sidebar.classList.remove('expanded');
                } else {
                    sidebar.classList.remove('collapsed');
                    sidebar.classList.add('expanded');
                }

                updateToggleIcon(shouldCollapse);
                localStorage.setItem('sidebarCollapsed', shouldCollapse.toString());
            }

            // Inicializar sidebar
            if (!isMobile()) {
                // Desktop: cargar estado guardado
                const savedState = localStorage.getItem('sidebarCollapsed');
                if (savedState !== null) {
                    updateSidebarState(savedState === 'true');
                } else {
                    // Por defecto expandido en desktop
                    updateSidebarState(false);
                }

                // Toggle desktop
                if (toggleBtn) {
                    toggleBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const isCollapsed = sidebar.classList.contains('collapsed');
                        updateSidebarState(!isCollapsed);
                    });
                }
            } else {
                // Mobile: siempre cerrado inicialmente
                closeMobileSidebar();
            }

            // Toggle mobile (hamburguesa)
            if (mobileToggleBtn) {
                mobileToggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const isOpen = sidebar.classList.contains('mobile-open');
                    if (isOpen) {
                        closeMobileSidebar();
                    } else {
                        openMobileSidebar();
                    }
                });
            }

            // Cerrar sidebar al hacer click en overlay
            if (overlay) {
                overlay.addEventListener('click', function() {
                    closeMobileSidebar();
                });
            }

            // Cerrar sidebar al hacer click en el logo (mobile)
            const sidebarLogo = sidebar.querySelector('.sidebar-header .logo');
            if (sidebarLogo) {
                sidebarLogo.addEventListener('click', function(e) {
                    if (isMobile()) {
                        e.preventDefault();
                        closeMobileSidebar();
                    }
                });
            }

            // Cerrar sidebar en mobile al hacer click en un link
            const navLinks = sidebar.querySelectorAll('.nav-item-cartify');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (isMobile()) {
                        closeMobileSidebar();
                    }
                });
            });

            // Actualizar en resize
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    const nowMobile = isMobile();
                    if (nowMobile !== currentIsMobile) {
                        currentIsMobile = nowMobile;
                        if (nowMobile) {
                            // Cambió a mobile: cerrar sidebar
                            closeMobileSidebar();
                        } else {
                            // Cambió a desktop: cargar estado guardado
                            const savedState = localStorage.getItem('sidebarCollapsed');
                            if (savedState !== null) {
                                updateSidebarState(savedState === 'true');
                            } else {
                                updateSidebarState(false);
                            }
                        }
                    }
                }, 250);
            });
        });

        // Manejo de notificaciones con SweetAlert2
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                window.Toast.fire({
                    icon: 'success',
                    title: '{{ session('success') }}'
                });
            @endif

            @if(session('error'))
                window.CartifySwal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ session('error') }}'
                });
            @endif

            @if(session('status'))
                window.Toast.fire({
                    icon: 'info',
                    title: '{{ session('status') }}'
                });
            @endif
        });

        // Interceptar errores de límite de suscripción en todas las peticiones fetch
        const originalFetch = window.fetch;
        window.fetch = async function(...args) {
            const response = await originalFetch(...args);

            // Clonar la respuesta para poder leerla múltiples veces
            const clonedResponse = response.clone();

            // Si es un error 403, verificar si es por límite de suscripción
            if (response.status === 403) {
                try {
                    const data = await clonedResponse.json();
                    if (data.error_code === 'SUBSCRIPTION_LIMIT_EXCEEDED') {
                        showSubscriptionLimitModal(data);
                        return response; // Retornar la respuesta original
                    }
                } catch (e) {
                    // Si no es JSON, continuar normalmente
                }
            }

            return response;
        };

        function showSubscriptionLimitModal(data) {
            const limitTypeNames = {
                'restaurants': 'Restaurantes',
                'users': 'Usuarios',
                'qr_codes': 'Códigos QR'
            };

            const limitTypeName = limitTypeNames[data.limit_type] || data.limit_type;

            window.CartifySwal.fire({
                icon: 'warning',
                title: 'Límite de Plan Alcanzado',
                html: `
                    <div class="text-start px-2">
                        <p class="mb-4 fs-5">Has alcanzado el límite de <strong>${limitTypeName}</strong> permitidos en tu plan actual.</p>
                        <p class="mb-0 fs-6 text-muted">Por favor, actualiza tu plan para poder crear más recursos.</p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Ver Planes',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#7c3aed',
                cancelButtonColor: '#6c757d',
                customClass: {
                    popup: 'swal2-popup-large',
                    title: 'swal2-title-large',
                    htmlContainer: 'swal2-html-large'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = data.upgrade_url || '{{ route("admin.subscription") }}';
                }
            });
        }

        // Exponer la función globalmente para uso manual si es necesario
        window.showSubscriptionLimitModal = showSubscriptionLimitModal;
    </script>
    @yield('scripts')
</body>
</html>
