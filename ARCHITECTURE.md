# Innowise E-Commerce – Architecture & Directory Structure

## Refactored Directory Structure

```
InnowiseECommerceSolutions/
├── app/
│   ├── DTO/
│   │   ├── BaseDTO.php
│   │   ├── CartItemDTO.php
│   │   ├── CategoryDTO.php
│   │   ├── OrderDTO.php
│   │   ├── ProductDTO.php
│   │   ├── ReviewDTO.php
│   │   ├── UpdateUserDTO.php
│   │   └── UploadImageDTO.php
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php
│   │   │   ├── CartController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── ExternalCategorySyncController.php
│   │   │   ├── ProductController.php
│   │   │   ├── RecommendationController.php
│   │   │   └── Product/
│   │   │       ├── GetCategoryProductsAction.php
│   │   │       ├── GetProductByIdAction.php
│   │   │       └── StoreProductAction.php
│   │   ├── Middleware/
│   │   │   └── CheckAdmin.php
│   │   ├── Requests/
│   │   │   ├── AddToCartRequest.php
│   │   │   ├── StoreCategoryRequest.php
│   │   │   ├── StoreOrderRequest.php
│   │   │   ├── StoreProductRequest.php
│   │   │   ├── StoreReviewRequest.php
│   │   │   ├── UpdateCartItemRequest.php
│   │   │   └── UpdateUserRequest.php
│   │   └── Resources/
│   │       ├── CartResource.php
│   │       ├── CategoryResource.php
│   │       └── ProductResource.php
│   ├── Models/
│   │   ├── CartItem.php
│   │   ├── Category.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── Product.php
│   │   ├── ProductImage.php
│   │   ├── ProductView.php
│   │   ├── Review.php
│   │   └── User.php
│   ├── Observers/
│   │   ├── ProductImageObserver.php
│   │   └── ProductObserver.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   ├── Repositories/
│   │   ├── Interfaces/
│   │   │   ├── CartItemRepositoryInterface.php
│   │   │   ├── CategoryRepositoryInterface.php
│   │   │   ├── OrderRepositoryInterface.php
│   │   │   ├── ProductRepositoryInterface.php
│   │   │   ├── ProductViewRepositoryInterface.php
│   │   │   ├── ReviewRepositoryInterface.php
│   │   │   └── UserRepositoryInterface.php
│   │   ├── CartItemRepository.php
│   │   ├── CategoryRepository.php
│   │   ├── OrderRepository.php
│   │   ├── ProductRepository.php
│   │   ├── ProductViewRepository.php
│   │   ├── ReviewRepository.php
│   │   └── UserRepository.php
│   └── Services/
│       ├── AuthService.php
│       ├── CartService.php
│       ├── CategoryService.php
│       ├── ExternalCategorySyncService.php
│       ├── FileService.php
│       ├── OrderService.php
│       ├── ProductService.php
│       ├── RecommendationService.php
│       └── ReviewService.php
├── config/
│   ├── filesystems.php
│   ├── services.php
│   └── sanctum.php
├── database/migrations/
├── resources/
│   ├── css/app.css
│   ├── js/
│   │   ├── api.js
│   │   ├── app.jsx
│   │   ├── components/
│   │   │   ├── AdminRoute.jsx
│   │   │   ├── CookieConsent.jsx
│   │   │   ├── ImageWithFallback.jsx
│   │   │   └── Navbar.jsx
│   │   ├── contexts/
│   │   │   ├── AuthContext.jsx
│   │   │   ├── ThemeContext.jsx
│   │   │   └── UserDropdown.jsx
│   │   └── pages/
│   │       ├── AdminPage.jsx
│   │       ├── AboutPage.jsx
│   │       ├── CartPage.jsx
│   │       ├── Catalog.jsx
│   │       ├── CategoriesPage.jsx
│   │       ├── CategoryProductsPage.jsx
│   │       ├── LoginPage.jsx
│   │       ├── SingleProductPage.jsx
│   │       └── ...
│   └── views/welcome.blade.php
├── routes/
│   ├── api.php
│   └── web.php
├── docker-compose.yml
├── .env.example
└── ARCHITECTURE.md
```

## Core Architecture (Clean Architecture + Service-Repository)

- **Controllers** → use **Services** → use **Repositories** and **DTOs**
- **DTOs** transfer data between layers
- **Repositories** only store/retrieve data; return S3 URL/path for media
- **FileService** handles upload/delete; **Observers** clean S3 on Product/Category delete

## Environment & Docker

- **MinIO** in `docker-compose.yml` for S3-compatible storage
- **.env** S3/MinIO: `AWS_ENDPOINT`, `AWS_USE_PATH_STYLE_ENDPOINT=true`, `FILESYSTEM_MEDIA_DISK`
- **External project**: `EXTERNAL_PROJECT_API_URL`, `EXTERNAL_PROJECT_API_KEY` for category sync
- **Queue worker**: `shop_worker` runs `php artisan queue:work`

## Cookie & Cart

- **Cookie consent** popup; accept → long-lived cart cookie; decline → volatile session
- **Cart** API: GET/POST/PUT/DELETE `/api/cart` with cookie-based `cart_session`

## Recommendations & Search

- **Product views** tracked in `product_views` by `user_id` or `view_session` cookie
- **Recommendations endpoints**: `GET /api/recommendations/home`
- **Recommendations endpoints**: `GET /api/products/{id}/recommendations`
- **Recommendations endpoints**: `POST /api/products/{id}/view`
- **Search facets**: `/api/products/search` supports `category_id`, `price_min`, `price_max`, `in_stock`
