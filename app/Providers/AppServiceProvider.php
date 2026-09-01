<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Laravel\Dusk\DuskServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Calificacion;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Para PDFs: embeber imágenes en base64 desde disco (no usar URLs HTTP bloqueadas por Cloudflare).
        Blade::directive('pdfAsset', function ($expression) {
            return "<?php
                \$p = trim($expression, \"'\\\"\");
                \$r = public_path(\$p);
                if (!is_readable(\$r)) {
                    \$resolved = realpath(\$r);
                    \$r = \$resolved ?: \$r;
                }
                if (is_readable(\$r)) {
                    \$ext = strtolower(pathinfo(\$r, PATHINFO_EXTENSION));
                    \$mime = match (\$ext) {
                        'jpg', 'jpeg' => 'image/jpeg',
                        'gif' => 'image/gif',
                        'webp' => 'image/webp',
                        default => 'image/png',
                    };
                    echo 'data:' . \$mime . ';base64,' . base64_encode(file_get_contents(\$r));
                }
            ?>";
        });

        Carbon::setLocale(config('app.locale'));
        Paginator::useBootstrap();

        // Compartir calificaciones pendientes para clientes en todas las vistas
        View::composer('layouts.app', function ($view) {
            if (Auth::check() && Auth::user()->UsRol === 'Cliente') {
                $calificacionesPendientes = Calificacion::with(['servicio.cliente', 'rm'])
                    ->where('ID_Cli', Auth::user()->id)
                    ->where('status', 'pending')
                    ->orderBy('created_at', 'desc')
                    ->get();

                $view->with('calificacionesPendientes', $calificacionesPendientes);
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local', 'testing') && class_exists(DuskServiceProvider::class)) {
            $this->app->register(DuskServiceProvider::class);
        }
    }
}
