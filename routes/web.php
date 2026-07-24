<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProduitController;

// ── AUTH ───────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ── MENU PUBLIC (accès client via QR code) ─────────
// ── MENU PUBLIC ─────────────────────────────────────
Route::prefix('menu')->name('menu.')->group(function () {
    Route::get('table/{uuid}',           [App\Http\Controllers\MenuPublicController::class, 'index'])->name('index');
    Route::get('table/{uuid}/suivi',     [App\Http\Controllers\MenuPublicController::class, 'suiviTable'])->name('suivi_table');
    Route::get('table/{uuid}/statut',    [App\Http\Controllers\MenuPublicController::class, 'statutCommandes'])->name('statut');
    Route::post('commander',             [App\Http\Controllers\MenuPublicController::class, 'commander'])->name('commander');
    Route::put('commande/{numero}/modifier', [App\Http\Controllers\MenuPublicController::class, 'modifierCommande'])->name('modifier');
    Route::get('commande/{numero}',      [App\Http\Controllers\MenuPublicController::class, 'suivi'])->name('suivi');
});

// ── APP (authentifié) ──────────────────────────────
Route::middleware(['auth', 'actif'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── NOTIFICATIONS ──────────────────────────────
    // (avant tout le reste pour éviter les conflits)
    Route::prefix('notifications')->name('notifs.')->group(function () {
        Route::get('/',            [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::patch('{n}/lue',    [App\Http\Controllers\NotificationController::class, 'marquerLue'])->name('lue');
        Route::post('toutes-lues', [App\Http\Controllers\NotificationController::class, 'toutesLues'])->name('toutes-lues');
    });

    // ── ADMIN ──────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users',      App\Http\Controllers\Admin\UserController::class);
        Route::resource('parametres', App\Http\Controllers\Admin\ParametreController::class);
        // ✅ Fix : pas de double préfixe "admin/admin"
        Route::patch('users/{user}/toggle',
            [App\Http\Controllers\Admin\UserController::class, 'toggleActif']
        )->name('users.toggle');
    });

    // ── ADMIN + CAISSIER ───────────────────────────
    Route::middleware('role:admin,caissier')->group(function () {

        // ✅ clients/search AVANT le resource pour éviter le conflit avec {client}
        Route::get('clients/search',
            [App\Http\Controllers\ClientController::class, 'search']
        )->name('clients.search');

        // Route::resource('categories', App\Http\Controllers\CategorieController::class);
        Route::resource('Recette',   App\Http\Controllers\ProduitController::class);
        // routes/web.php ou api.php
        Route::patch('Recette/{produit}/toggle', [ProduitController::class, 'toggleDisponible']);
        Route::resource('clients',    App\Http\Controllers\ClientController::class);
        Route::resource('depenses',   App\Http\Controllers\DepenseController::class);

        Route::resource('categories', App\Http\Controllers\CategorieController::class);
        Route::patch('categories/{categorie}/toggle', [App\Http\Controllers\CategorieController::class, 'toggleActif'])->name('categories.toggle');
        Route::post('categories/reordonner', [App\Http\Controllers\CategorieController::class, 'reordonner'])->name('categories.reordonner');
    

        Route::get('rapports',
            [App\Http\Controllers\RapportController::class, 'index']
        )->name('rapports');

        // Stock — ✅ routes statiques AVANT les routes avec paramètre {stock}
        Route::post('stocks/entree',
            [App\Http\Controllers\StockController::class, 'entree']
        )->name('stocks.entree');

        Route::post('stocks/sortie',
            [App\Http\Controllers\StockController::class, 'sortie']
        )->name('stocks.sortie');

        Route::get('stocks',
            [App\Http\Controllers\StockController::class, 'index']
        )->name('stocks.index');

        Route::get('stocks/{stock}',
            [App\Http\Controllers\StockController::class, 'show']
        )->name('stocks.show');

        Route::patch('stocks/{stock}/seuil',
            [App\Http\Controllers\StockController::class, 'ajusterSeuil']
        )->name('stocks.seuil');
    });

    // ── ADMIN + SERVEUR + CAISSIER ─────────────────
    Route::middleware('role:admin,serveur,caissier')->group(function () {

        // ✅ Route spécifique AVANT le resource commandes
        Route::delete(
            'commandes/{commande}/items/{item}',
            [App\Http\Controllers\CommandeController::class, 'removeItem']
        )->name('commandes.items.destroy');

        Route::patch('tables/{table}/statut', [App\Http\Controllers\TableController::class, 'changerStatut'])->name('tables.statut');
        Route::resource('tables',    App\Http\Controllers\TableController::class);
        Route::resource('commandes', App\Http\Controllers\CommandeController::class);
    });

    // ── CAISSE ─────────────────────────────────────
    Route::middleware('role:admin,caissier')->prefix('caisse')->name('caisse.')->group(function () {
        Route::get('/',                          [App\Http\Controllers\CaisseController::class, 'index'])->name('index');
        Route::get('pretes',                     [App\Http\Controllers\CaisseController::class, 'commandesPretes'])->name('pretes');
        Route::get('historique',                 [App\Http\Controllers\CaisseController::class, 'historique'])->name('historique');
        Route::get('historique/{paiement}',      [App\Http\Controllers\CaisseController::class, 'detailPaiement'])->name('paiement.detail');
        Route::post('encaisser/{commande}',      [App\Http\Controllers\CaisseController::class, 'encaisser'])->name('encaisser');
    });

    // ── CUISINE ────────────────────────────────────
    Route::middleware('role:admin,cuisinier')->prefix('cuisine')->name('cuisine.')->group(function () {
        // ✅ Fix : "poll" sans double préfixe "cuisine/cuisine/poll"
        Route::get('poll', [App\Http\Controllers\CuisineController::class, 'poll'])->name('poll');
        Route::get('/',    [App\Http\Controllers\CuisineController::class, 'index'])->name('index');
        Route::patch('{commande}/prendre',
            [App\Http\Controllers\CuisineController::class, 'prendre']
        )->name('prendre');
        Route::patch('{commande}/prete',
            [App\Http\Controllers\CuisineController::class, 'prete']
        )->name('prete');
    });

    // Modifier profil
    Route::get('mon-compte',           [App\Http\Controllers\ProfilController::class, 'index'])->name('profil.index');
    Route::put('mon-compte/password',  [App\Http\Controllers\ProfilController::class, 'updatePassword'])->name('profil.password');
    Route::put('mon-compte/infos',     [App\Http\Controllers\ProfilController::class, 'updateInfos'])->name('profil.infos');

});