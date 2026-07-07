<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Service Unavailable - Smart Tasks</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        background: '#0a0a0a',
                        foreground: '#ededed',
                        emerald: { 600: '#059669', 700: '#047857' }
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: theme('colors.background'); color: theme('colors.foreground'); }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="flex min-h-screen flex-col items-center justify-center p-4">
    <!-- Ambient background glow -->
    <div class="absolute inset-x-0 top-0 -z-10 h-[300px]" style="background:linear-gradient(180deg, rgba(5, 150, 105, 0.15) 0%, rgba(5, 150, 105, 0) 100%)"></div>
    
    <div class="glass-card max-w-md w-full rounded-2xl p-8 text-center shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-600 to-teal-400"></div>
        
        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-600/10 text-emerald-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        
        <h1 class="text-2xl font-semibold tracking-tight text-white mb-2">Systems Currently Offline</h1>
        <p class="text-sm text-gray-400 mb-8 leading-relaxed">
            Smart Tasks is unable to connect to the primary database. Our engineering team has been automatically notified. Please try again in a few minutes.
        </p>
        
        <button onclick="window.location.reload()" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-6 text-sm font-medium text-white transition-colors hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 focus:ring-offset-background w-full">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"></path>
                <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"></path>
            </svg>
            Retry Connection
        </button>
    </div>
    
    <div class="mt-8 text-xs text-gray-600">
        Error Code: DB_CONN_TIMEOUT &bull; <?= date('Y-m-d H:i:s T') ?>
    </div>
</body>
</html>
