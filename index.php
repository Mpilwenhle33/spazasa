<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Load configuration
require_once __DIR__ . '/config/DatabaseConnection.php';
require_once __DIR__ . '/config/ValidationHelper.php';

// Load models
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Product.php';
require_once __DIR__ . '/models/Category.php';
require_once __DIR__ . '/models/Cart.php';
require_once __DIR__ . '/models/Message.php';
require_once __DIR__ . '/models/Order.php';
require_once __DIR__ . '/models/Wishlist.php';

// Load controllers
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ProductController.php';
require_once __DIR__ . '/controllers/CartController.php';
require_once __DIR__ . '/controllers/CheckoutController.php';
require_once __DIR__ . '/controllers/ProfileController.php';
require_once __DIR__ . '/controllers/MessageController.php';
require_once __DIR__ . '/controllers/LanguageController.php';

// Set language from session or default
$currentLang = $_SESSION['language_pref'] ?? 'english';

// Translation array
$lang = [
    'english' => [
        'home' => 'Home',
        'language' => 'Language',
        'marketplace' => 'Marketplace',
        'cart' => 'Cart',
        'login' => 'Login',
        'register' => 'Register',
        'logout' => 'Logout',
        'search' => 'Search products...',
        'sell' => 'Sell',
        'view' => 'VIEW',
        'sold' => 'SOLD',
        'new' => 'NEW',
        'no_items' => 'No items found. Be the first to list something!',
        'welcome_back' => 'Welcome Back',
        'sign_up' => 'Sign Up',
        'join' => 'Join SpazaSa',
        'username' => 'Username',
        'full_name' => 'Full Name',
        'email' => 'Email',
        'password' => 'Password',
        'phone' => 'Phone',
        'location' => 'Location',
        'postal_code' => 'Postal Code',
        'list_item' => 'List Your Item',
        'title' => 'Title',
        'description' => 'Description',
        'price' => 'Price (ZAR)',
        'category' => 'Category',
        'condition' => 'Condition',
        'publish' => 'List Item',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'add_to_cart' => 'Add to Cart',
        'remove' => 'Remove',
        'total' => 'Total',
        'checkout' => 'Checkout',
        'continue_shopping' => 'Continue Shopping',
        'empty_cart' => 'Your cart is empty.',
        'profile' => 'Profile',
        'purchase_history' => 'Purchase History',
        'sales_history' => 'Sales History',
        'no_purchases' => 'No purchases yet.',
        'no_sales' => 'No sales yet.',
        'messages' => 'Messages',
        'no_messages' => 'No messages yet.',
        'chat_with' => 'Chat with',
        'type_message' => 'Type a message...',
        'send' => 'Send',
        'receipt' => 'Receipt',
        'payment_reference' => 'Payment Reference',
        'order_details' => 'Order Details',
        'thank_you' => 'Thank you for your purchase!',
        'print' => 'Print Receipt',
        'my_listings' => 'My Listings',
        'my_orders' => 'My Orders'
    ],
    'isizulu' => [
        'home' => 'Ikhaya',
        'language' => 'Ulimi',
        'marketplace' => 'Imakethe',
        'cart' => 'Ingolobane',
        'login' => 'Ngena',
        'register' => 'Bhalisa',
        'logout' => 'Phuma',
        'search' => 'Sesha imikhiqizo...',
        'sell' => 'Thengisa',
        'view' => 'BUKA',
        'sold' => 'KUTSHENZISIWE',
        'new' => 'ENTSHA',
        'no_items' => 'Azikho izinto ezitholakalayo. Yiba ngowokuqala ukufaka okuthile!',
        'welcome_back' => 'Sawubona futhi',
        'sign_up' => 'Bhalisa',
        'join' => 'Hlanganyela neSpazaSa',
        'username' => 'Igama lomsebenzisi',
        'full_name' => 'Igama eliphelele',
        'email' => 'I-imeyili',
        'password' => 'Iphasiwedi',
        'phone' => 'Ucingo',
        'location' => 'Indawo',
        'postal_code' => 'Ikhodi yeposi',
        'list_item' => 'Faka Into Yakho',
        'title' => 'Isihloko',
        'description' => 'Incazelo',
        'price' => 'Intengo (ZAR)',
        'category' => 'Umkhakha',
        'condition' => 'Isimo',
        'publish' => 'Shicilela',
        'edit' => 'Hlela',
        'delete' => 'Susa',
        'add_to_cart' => 'Faka Engolobaneni',
        'remove' => 'Susa',
        'total' => 'Isamba',
        'checkout' => 'Khokha',
        'continue_shopping' => 'Qhubeka Uthenga',
        'empty_cart' => 'Ingolobane yakho ingenalutho.',
        'profile' => 'Iphrofayili',
        'purchase_history' => 'Umlando Wokuthenga',
        'sales_history' => 'Umlando Wokuthengisa',
        'no_purchases' => 'Awukathengi noma yini.',
        'no_sales' => 'Awukathengisi noma yini.',
        'messages' => 'Imiyalezo',
        'no_messages' => 'Ayikho imiyalezo okwamanje.',
        'chat_with' => 'Xoxa no',
        'type_message' => 'Bhala umyalezo...',
        'send' => 'Thumela',
        'receipt' => 'Irisidi',
        'payment_reference' => 'Inkomba Yokukhokha',
        'order_details' => 'Imininingwane Ye-oda',
        'thank_you' => 'Siyabonga ngokuthenga kwakho!',
        'print' => 'Phrinta Irisidi',
        'my_listings' => 'Izinto Zami',
        'my_orders' => 'Ama-oda Ami'
    ],
    'sesotho' => [
        'home' => 'Lapeng',
        'language' => 'Puo',
        'marketplace' => 'Mmaraka',
        'cart' => 'Karolo',
        'login' => 'Kena',
        'register' => 'Ngodisa',
        'logout' => 'Tswa',
        'search' => 'Batla lihlahisoa...',
        'sell' => 'Rekisa',
        'view' => 'SHEBA',
        'sold' => 'E REKISITSOENG',
        'new' => 'E NTJHA',
        'no_items' => 'Ha ho na lintho tse fumanehang. Eba oa pele ho thathamisa ntho!',
        'welcome_back' => 'Dumela hape',
        'sign_up' => 'Ngodisa',
        'join' => 'Kenela SpazaSa',
        'username' => 'Lebitso la mosebedisi',
        'full_name' => 'Lebitso le felletseng',
        'email' => 'Imeile',
        'password' => 'Phasewete',
        'phone' => 'Mohala',
        'location' => 'Sebaka',
        'postal_code' => 'Khoutu ea poso',
        'list_item' => 'Thathamisa Ntho ea Hao',
        'title' => 'Sehlooho',
        'description' => 'Tlhaloso',
        'price' => 'Theko (ZAR)',
        'category' => 'Sehlopha',
        'condition' => 'Maemo',
        'publish' => 'Phatlalatsa',
        'edit' => 'Fetola',
        'delete' => 'Tlosa',
        'add_to_cart' => 'Kenya Karolong',
        'remove' => 'Tlosa',
        'total' => 'Kakaretso',
        'checkout' => 'Lefa',
        'continue_shopping' => 'Tswela Pele ho Reka',
        'empty_cart' => 'Karolo ea hao ha e na letho.',
        'profile' => 'Profilo',
        'purchase_history' => 'Nalane ea Theko',
        'sales_history' => 'Nalane ea Thekiso',
        'no_purchases' => 'Ha u so reke letho.',
        'no_sales' => 'Ha u so rekise letho.',
        'messages' => 'Melaetsa',
        'no_messages' => 'Ha ho na melaetsa hajoale.',
        'chat_with' => 'Bua le',
        'type_message' => 'Ngola molaetsa...',
        'send' => 'Romela',
        'receipt' => 'Rasiti',
        'payment_reference' => 'Setšupiso sa Tefo',
        'order_details' => 'Lintlha tsa Odara',
        'thank_you' => 'Kea leboha ka theko ea hau!',
        'print' => 'Hatisa Rasiti',
        'my_listings' => 'Lintho Tsa Ka',
        'my_orders' => 'Liodara Tsa Ka'
    ],
    'afrikaans' => [
        'home' => 'Tuis',
        'language' => 'Taal',
        'marketplace' => 'Markplek',
        'cart' => 'Mandjie',
        'login' => 'Teken In',
        'register' => 'Registreer',
        'logout' => 'Teken Uit',
        'search' => 'Soek produkte...',
        'sell' => 'Verkoop',
        'view' => 'BEKYK',
        'sold' => 'VERKOOP',
        'new' => 'NUUT',
        'no_items' => 'Geen items gevind nie. Wees die eerste om iets te lys!',
        'welcome_back' => 'Welkom Terug',
        'sign_up' => 'Registreer',
        'join' => 'Sluit aan by SpazaSa',
        'username' => 'Gebruikersnaam',
        'full_name' => 'Volle Naam',
        'email' => 'E-pos',
        'password' => 'Wagwoord',
        'phone' => 'Telefoon',
        'location' => 'Ligging',
        'postal_code' => 'Poskode',
        'list_item' => 'Lys Jou Item',
        'title' => 'Titel',
        'description' => 'Beskrywing',
        'price' => 'Prys (ZAR)',
        'category' => 'Kategorie',
        'condition' => 'Toestand',
        'publish' => 'Publiseer',
        'edit' => 'Wysig',
        'delete' => 'Verwyder',
        'add_to_cart' => 'Voeg By Mandjie',
        'remove' => 'Verwyder',
        'total' => 'Totaal',
        'checkout' => 'Betaal',
        'continue_shopping' => 'Gaan Voort',
        'empty_cart' => 'Jou mandjie is leeg.',
        'profile' => 'Profiel',
        'purchase_history' => 'Aankoopgeskiedenis',
        'sales_history' => 'Verkoopsgeskiedenis',
        'no_purchases' => 'Geen aankope nog nie.',
        'no_sales' => 'Geen verkope nog nie.',
        'messages' => 'Boodskappe',
        'no_messages' => 'Geen boodskappe nog nie.',
        'chat_with' => 'Gesels met',
        'type_message' => 'Tik \'n boodskap...',
        'send' => 'Stuur',
        'receipt' => 'Kwitansie',
        'payment_reference' => 'Betalingsverwysing',
        'order_details' => 'Bestelbesonderhede',
        'thank_you' => 'Dankie vir jou aankoop!',
        'print' => 'Druk Kwitansie',
        'my_listings' => 'My Items',
        'my_orders' => 'My Bestellings'
    ]
];

// Helper function to translate
function t($key) {
    global $lang, $currentLang;
    return $lang[$currentLang][$key] ?? $lang['english'][$key] ?? $key;
}

// Get action
$action = $_GET['action'] ?? 'home';

// Helper function to get categories
function getCategories() {
    $categoryModel = new Category();
    return $categoryModel->getAll();
}

// Route handling
switch ($action) {
    case 'home':
        include __DIR__ . '/views/home.php';
        break;
        
    case 'language':
        include __DIR__ . '/views/language.php';
        break;
        
    case 'set-language':
        $controller = new LanguageController();
        $controller->setLanguage();
        break;
        
    case 'marketplace':
        $productModel = new Product();
        // Check if filtering by seller
        if (isset($_GET['seller']) && is_numeric($_GET['seller'])) {
            $products = $productModel->getBySeller($_GET['seller']);
        } else {
            $products = $productModel->getApproved();
        }
        $categories = getCategories();
        include __DIR__ . '/views/marketplace.php';
        break;
        
    case 'detail':
        $productModel = new Product();
        $productId = $_GET['id'] ?? 0;
        $product = $productModel->getById($productId);
        if (!$product) {
            header('Location: index.php?action=marketplace');
            exit;
        }
        $productModel->incrementViews($productId);
        $images = $productModel->getImages($productId);
        include __DIR__ . '/views/detail.php';
        break;
    
    case 'search':
        $productModel = new Product();
        $search = $_GET['q'] ?? '';
        $products = $productModel->search($search);
        $categories = getCategories();
        include __DIR__ . '/views/marketplace.php';
        break;
        
    case 'category':
        $productModel = new Product();
        $categoryId = $_GET['id'] ?? 0;
        $products = $productModel->getByCategory($categoryId);
        $categories = getCategories();
        include __DIR__ . '/views/marketplace.php';
        break;
    
    case 'login':
        $controller = new AuthController();
        $controller->showLogin();
        break;
        
    case 'register':
        $controller = new AuthController();
        $controller->showRegister();
        break;
        
    case 'do-login':
        $controller = new AuthController();
        $controller->login();
        break;
        
    case 'do-register':
        $controller = new AuthController();
        $controller->register();
        break;
        
    case 'logout':
        $controller = new AuthController();
        $controller->logout();
        break;
    
    case 'cart':
        $controller = new CartController();
        $controller->showCart();
        break;
        
    case 'add-to-cart':
        $controller = new CartController();
        $controller->addToCart();
        break;
        
    case 'remove-from-cart':
        $controller = new CartController();
        $controller->removeFromCart();
        break;
        
    case 'cart-count':
        $controller = new CartController();
        $controller->getCount();
        break;
        
    case 'wishlist-toggle':
        $controller = new CartController();
        $controller->toggleWishlist();
        break;
        
    case 'checkout':
        $controller = new CheckoutController();
        $controller->checkout();
        break;
        
    case 'receipt':
        $controller = new CheckoutController();
        $controller->showReceipt();
        break;
    
    case 'profile':
        $controller = new ProfileController();
        $controller->showProfile();
        break;
        
    case 'update-profile':
        $controller = new ProfileController();
        $controller->updateProfile();
        break;
    
    case 'orders':
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        // Redirect to profile which already shows orders
        header('Location: index.php?action=profile');
        exit;
        break;
    
    case 'messages':
        $controller = new MessageController();
        $controller->showMessages();
        break;
        
    case 'chat':
        $controller = new MessageController();
        $controller->showChat();
        break;
        
    case 'send-message':
        $controller = new MessageController();
        $controller->sendMessage();
        break;
        
    case 'get-messages':
        $controller = new MessageController();
        $controller->getMessages();
        break;
    
    case 'sell':
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        $categories = getCategories();
        include __DIR__ . '/views/sell.php';
        break;
        
    case 'do-sell':
        $controller = new ProductController();
        $controller->sell();
        break;
        
    case 'edit-product':
        $controller = new ProductController();
        $controller->showEdit();
        break;
        
    case 'do-edit-product':
        $controller = new ProductController();
        $controller->edit();
        break;
        
    case 'delete-product':
        $controller = new ProductController();
        $controller->delete();
        break;
    
    default:
        header('Location: index.php?action=home');
        break;
}
