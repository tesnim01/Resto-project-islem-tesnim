# Resto Project

This repository contains a Symfony-based restaurant management application. It exposes both a JSON API (with JWT authentication) and a traditional server-rendered web interface for admins and customers.

---

##  Stack & Libraries

- **Platform**: PHP >= 8.2
- **Framework**: Symfony 7.4 (skeleton)
- **ORM**: Doctrine ORM + Migrations
- **Authentication**:
  - `symfony/security-bundle` for firewall configuration
  - `lexik/jwt-authentication-bundle` for stateless JWT tokens
- **Frontend helpers**:
  - Twig + `twig/extra-bundle` for templating
  - `symfony/stimulus-bundle` & `symfony/ux-turbo` for progressive enhancement
- **Utilities & others**:
  - Symfony components (`console`, `form`, `validator`, `mailer`, etc.)
  - Monolog for logging
  - PHPUnit + Symfony test packages for automated tests
  - `phpdocumentor/reflection-docblock`, `phpstan/phpdoc-parser` for static analysis

The full list of dependencies is declared in `composer.json`.

---

##  JWT Authentication (API)

The API lives under the `/api` path and is completely stateless.

1. **Login**
   - `POST /api/login` with JSON `{ "email": "...", "password": "..." }`.
   - Handled by `json_login` firewall; on success LexikJWT generates a token.
   - `JWTCreatedListener` adds custom claims (`id`, `name`, `roles`) to the payload.

2. **Register**
   - `POST /api/register` to create a new customer.
   - Admins can register other admins via `POST /api/register/admin` (protected via `IsGranted('ROLE_ADMIN')`).

3. **Protected endpoints**
   - Firewalled by JWT token (`Bearer` header) in `security.yaml` under the `api` firewall.
   - Access-control rules define which roles may reach which routes (see `security.yaml`).
   - Controllers use `#[IsGranted]` attributes to restrict actions (e.g. admin-only operations).

The user provider loads from the `App\Entity\User` hierarchy (single table inheritance for `Admin` and `Customer`).

---

##  Admin Interface

The admin-facing UI is a server-rendered Symfony application accessible via the normal `main` firewall.

- The admin dashboard lives at `/dashboard/admin` (redirected from `/` when logged in).
- Admins can manage users, products, categories, orders, and reviews through controllers under `App\Controller\Web` and API endpoints beneath `/api/admin`.
- Routes are defined via PHP attributes; see the `Web` controllers for the mapping.
- Forms and CSRF protection are provided by Symfony's form system.

Authorization is enforced both at the route level (`access_control`) and inside controllers using `IsGranted('ROLE_ADMIN')`.

---

##  Customer / User Interface Routing

Customers interact through the web UI and the API.

- Public routes:
  - `/login` and `/register` render the authentication forms.
  - `/` redirects to either login or the appropriate dashboard depending on the user’s role.
- Once logged in, customers are sent to `/dashboard/customer`.
- API endpoints that customers use include `/api/orders` and `/api/reviews` (role protected).
- Product browsing is available both via web and via `/api/public/products` for unauthenticated access.

Web controllers are located in `src/Controller/Web/*` and templates in `templates/` (see subdirectories for pages).

---

##  API Routing Overview

Most of the JSON API is rooted at `/api` and uses the following patterns:

- `POST /api/login` – obtain JWT.
- `POST /api/register` – create customer.
- `GET /api/public/products` – list products (optional query params `category` and `available`).
- `GET /api/public/products/{id}` – show single product.
- `GET /api/categories`, `/api/products`, etc. for other resources (see controllers).
- Admin-only paths begin with `/api/admin`.

Use `bin/console debug:router` to list all registered routes and their methods.

---

##  Getting Started

```bash
cp .env .env.local        # configure your database and JWT keys
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console lexik:jwt:generate-keypair   # or use provided batch scripts

# run the built‑in server
symfony server:start
```

Use the provided `generate_jwt_keys.*` scripts to create the private/public key pair required by LexikJWT.

