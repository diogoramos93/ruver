<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Organizer\SettingsController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use App\Http\Controllers\FavoritosController;
use App\Http\Controllers\CheckinController;

// ==========================================

// 🌐 GRUPO WHITE-LABEL (Domínio Personalizado do Organizador)

// ==========================================

Route::domain('{domain}')->middleware(\App\Http\Middleware\IdentifyTenant::class)->group(function () {

    Route::get('/', function ($domain) {

        $organizer = request()->get('currentOrganizer');

        if (!$organizer) {

            abort(404, 'Organizador não encontrado para este domínio.');
        }

        return app(PublicController::class)->index(request());
    })->name('tenant.home');

    Route::get('/evento/{slug}', [PublicController::class, 'show'])->name('tenant.event.show');

    Route::get('/evento/{slug}/resultados', [PublicController::class, 'resultados'])->name('tenant.event.resultados');
});

// ==========================================

// 🛒 ROTAS PÚBLICAS DO SITE PRINCIPAL (Ruver)

// ==========================================

Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/evento/{slug}', [PublicController::class, 'show'])->name('event.show');
Route::get('/evento/{slug}/resultados', [PublicController::class, 'resultados'])->name('event.resultados');
Route::get('/resultados', [PublicController::class, 'listaResultados'])->name('resultados.index');

// ==========================================

// 🎫 INGRESSO COMPARTILHÁVEL PÚBLICO

// ==========================================

Route::get('/ingresso/{participant}', [CheckoutController::class, 'showTicket'])->name('ticket.show');

// Rota padrão do Breeze após logar

Route::get('/dashboard', function () {

    $user = auth()->user();

    if ($user->role === 'admin') {

        return redirect('/admin');
    } elseif ($user->role === 'organizador') {

        return redirect('/painel');
    }

    return redirect('/minha-conta');
})->middleware(['auth', 'verified'])->name('dashboard');

// 1. Painel do CLIENTE

Route::middleware(['auth', 'role:cliente'])->group(function () {

    Route::get('/minha-conta', function () {

        return view('dashboard');
    })->name('cliente.dashboard');
});

// 2. Painel do ORGANIZADOR

Route::middleware(['auth', 'role:organizador'])->prefix('painel')->name('organizer.')->group(function () {

    Route::get('/', function () {

        return redirect()->route('organizer.events.index');
    })->name('dashboard');
    Route::get('/events', [\App\Http\Controllers\Organizer\EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [\App\Http\Controllers\Organizer\EventController::class, 'create'])->name('events.create');
    Route::post('/events', [\App\Http\Controllers\Organizer\EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [\App\Http\Controllers\Organizer\EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [\App\Http\Controllers\Organizer\EventController::class, 'update'])->name('events.update');
    Route::get('/financeiro-geral', [\App\Http\Controllers\Organizer\FinancialGlobalController::class, 'index'])->name('financial.global');
    Route::get('/events/{event}/categorias', [\App\Http\Controllers\Organizer\TicketCategoryController::class, 'index'])->name('events.categories.index');
    Route::post('/events/{event}/categorias', [\App\Http\Controllers\Organizer\TicketCategoryController::class, 'store'])->name('events.categories.store');
    Route::delete('/events/{event}/categorias/{category}', [\App\Http\Controllers\Organizer\TicketCategoryController::class, 'destroy'])->name('events.categories.destroy');
    Route::get('/events/{event}/tickets', [\App\Http\Controllers\Organizer\TicketController::class, 'index'])->name('events.tickets.index');
    Route::get('/events/{event}/tickets/create', [\App\Http\Controllers\Organizer\TicketController::class, 'create'])->name('events.tickets.create');
    Route::post('/events/{event}/tickets', [\App\Http\Controllers\Organizer\TicketController::class, 'store'])->name('events.tickets.store');
    Route::get('/events/{event}/tickets/{ticket}/edit', [\App\Http\Controllers\Organizer\TicketController::class, 'edit'])->name('events.tickets.edit');
    Route::delete('/events/{event}/tickets/{ticket}', [\App\Http\Controllers\Organizer\TicketController::class, 'destroy'])->name('events.tickets.destroy');
    Route::put('/events/{event}/tickets/{ticket}', [\App\Http\Controllers\Organizer\TicketController::class, 'update'])->name('events.tickets.update');
    Route::get('/events/{event}/cupons', [\App\Http\Controllers\Organizer\CouponController::class, 'index'])->name('events.coupons.index');
    Route::post('/events/{event}/cupons', [\App\Http\Controllers\Organizer\CouponController::class, 'store'])->name('events.coupons.store');
    Route::delete('/events/{event}/cupons/{coupon}', [\App\Http\Controllers\Organizer\CouponController::class, 'destroy'])->name('events.coupons.destroy');
    Route::patch('/events/{event}/cupons/{coupon}/toggle', [\App\Http\Controllers\Organizer\CouponController::class, 'toggleActive'])->name('events.coupons.toggle');
    Route::get('/events/{event}/participantes', [\App\Http\Controllers\Organizer\ParticipantController::class, 'index'])->name('events.participants.index');
    Route::put('/events/{event}/participantes/{participant}/produtos', [\App\Http\Controllers\Organizer\ParticipantController::class, 'updateProducts'])->name('events.participants.updateProducts');
    Route::get('/events/{event}/financeiro', [\App\Http\Controllers\Organizer\EventController::class, 'financial'])->name('events.financial');
    Route::get('/produtos', [\App\Http\Controllers\Organizer\ProductController::class, 'index'])->name('products.index');
    Route::get('/produtos/criar', [\App\Http\Controllers\Organizer\ProductController::class, 'create'])->name('products.create');
    Route::post('/produtos', [\App\Http\Controllers\Organizer\ProductController::class, 'store'])->name('products.store');
    Route::get('/produtos/{product}/editar', [\App\Http\Controllers\Organizer\ProductController::class, 'edit'])->name('products.edit');
    Route::put('/produtos/{product}', [\App\Http\Controllers\Organizer\ProductController::class, 'update'])->name('products.update');
    Route::delete('/produtos/{product}', [\App\Http\Controllers\Organizer\ProductController::class, 'destroy'])->name('products.destroy');

    // ==========================================

    // FORMULÁRIOS DINÂMICOS POR EVENTO

    // ==========================================

    // Lista os formulários do evento

    Route::get(

        '/events/{event}/formularios',

        [\App\Http\Controllers\Organizer\FormController::class, 'index']

    )->name('events.forms.index');

    // Tela de criação de formulário

    Route::get(

        '/events/{event}/formularios/criar',

        [\App\Http\Controllers\Organizer\FormController::class, 'create']

    )->name('events.forms.create');

    // Salva um novo formulário

    Route::post(

        '/events/{event}/formularios',

        [\App\Http\Controllers\Organizer\FormController::class, 'store']

    )->name('events.forms.store');

    // Tela de edição de formulário

    Route::get(

        '/events/{event}/formularios/{form}/editar',

        [\App\Http\Controllers\Organizer\FormController::class, 'edit']

    )->name('events.forms.edit');

    // Atualiza um formulário

    Route::put(

        '/events/{event}/formularios/{form}',

        [\App\Http\Controllers\Organizer\FormController::class, 'update']

    )->name('events.forms.update');

    // Ativa ou desativa um formulário

    Route::patch(

        '/events/{event}/formularios/{form}/alternar',

        [\App\Http\Controllers\Organizer\FormController::class, 'toggle']

    )->name('events.forms.toggle');

    // Exclui um formulário

    Route::delete(

        '/events/{event}/formularios/{form}',

        [\App\Http\Controllers\Organizer\FormController::class, 'destroy']

    )->name('events.forms.destroy');

    Route::get('/configuracoes', [SettingsController::class, 'index'])->name('settings.index');

    Route::post('/configuracoes', [SettingsController::class, 'update'])->name('settings.update');
});

// 3. Painel do ADMIN

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/organizers', [\App\Http\Controllers\Admin\OrganizerController::class, 'index'])->name('organizers.index');
    Route::get('/organizers/create', [\App\Http\Controllers\Admin\OrganizerController::class, 'create'])->name('organizers.create');
    Route::post('/organizers', [\App\Http\Controllers\Admin\OrganizerController::class, 'store'])->name('organizers.store');
    Route::get('/organizers/{organizer}', [\App\Http\Controllers\Admin\OrganizerController::class, 'show'])->name('organizers.show');
    Route::get('/organizers/{organizer}/edit', [\App\Http\Controllers\Admin\OrganizerController::class, 'edit'])->name('organizers.edit');
    Route::put('/organizers/{organizer}', [\App\Http\Controllers\Admin\OrganizerController::class, 'update'])->name('organizers.update');
    Route::get('/events', [\App\Http\Controllers\Admin\EventController::class, 'index'])->name('events.index');
    Route::delete('/events/{event}', [\App\Http\Controllers\Admin\EventController::class, 'destroy'])->name('events.destroy');
});

// Rotas Comprador Atleta

Route::middleware(['auth'])->group(function () {

    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/checkout/cupom/validar', [CheckoutController::class, 'validarCupom'])->name('checkout.cupom.validar');
    Route::get('/meus-favoritos', [FavoritosController::class, 'index'])->name('my.favorites');
    Route::post('/favoritar/{event}', [FavoritosController::class, 'toggle'])->name('favoritos.toggle');
    Route::get('/favoritar/{event}/adicionar', function (\App\Models\Event $event) {

        auth()->user()->favoritos()->syncWithoutDetaching([$event->id]);

        return redirect()->route('event.show', $event->slug ?? $event->id)->with('success', 'Evento salvo nos favoritos com sucesso!');
    })->name('favoritos.add');
});

// Rotas de Compra & Portaria

Route::middleware(['auth'])->group(function () {

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/pedido/{order}/pagamento', [CheckoutController::class, 'paymentScreen'])->name('order.payment');
    Route::get('/pedido/{order}/status', [CheckoutController::class, 'checkStatus'])->name('order.check_status');

    // PORTARIA (Exige Login para barrar fraudadores lendo QR Code de penetra)
    Route::get('/validar/{id}/{documento}', [CheckinController::class, 'validar'])->name('checkin.validar');
    Route::get('/meus-ingressos', function () {

        $orders = auth()->user()->orders()->with(['ticket.event', 'participants'])->latest()->paginate(5);

        return view('my-tickets', compact('orders'));
    })->name('my.tickets');
});

// Webhook do Asaas

Route::post('/webhook/asaas', [\App\Http\Controllers\WebhookAsaasController::class, 'handle'])->name('webhook.asaas');

// Rotas de autenticação do Breeze

require __DIR__ . '/auth.php';

// Rota de Instalação (Desativada por segurança para proteger o banco de produção)
// Route::get('/instalar-sistema', function (Request $request) {
//     if ($request->query('token') !== 'minha-senha-secreta-123') {
//         return response('Acesso negado. Token inválido.', 403);
//     }
//     try {
//         Artisan::call('optimize:clear');
//         $output = "Cache limpo...<br>";
//         Artisan::call('storage:link');
//         $output .= "Pasta de imagens vinculada...<br>";
//         Artisan::call('migrate:fresh', [
//             '--force' => true,
//             '--seed' => true
//         ]);
//         $output .= "Banco de dados recriado e zerado com sucesso!...<br>";
//         return $output . "<br><h2 style='color:green'>INSTALAÇÃO CONCLUÍDA! 🎉</h2><br><strong style='color:red'>⚠️ APAGUE ESSA ROTA DEPOIS!</strong>";
//     } catch (\Exception $e) {
//         return "Erro durante a instalação: " . $e->getMessage();
//     }
// });
