# Ola Store Electronics - Premium E-commerce Website

A modern, minimalist e-commerce website inspired by Apple's design aesthetic, built with HTML, CSS/SCSS, JavaScript, PHP, and MySQL.

## Features

- **Modern Design**: Clean, minimalist UI with Liquid Glass effects and smooth animations
- **Fully Responsive**: Optimized for mobile, tablet, and desktop
- **Complete E-commerce**: Product catalog, shopping cart, checkout, user accounts
- **Admin Panel**: Product management, order tracking, analytics dashboard
- **Security**: Secure authentication, hashed passwords, session management
- **SEO Optimized**: Meta tags, friendly URLs, proper page structure

## Technology Stack

- **Frontend**: HTML5, CSS3/SCSS, JavaScript (ES6+)
- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Design**: Apple-inspired minimalist aesthetic with Liquid Glass effects

## Project Structure

```
ola-store/
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── scss/
├── includes/
│   ├── config.php
│   ├── database.php
│   ├── functions.php
│   └── auth.php
├── admin/
│   ├── dashboard.php
│   ├── products.php
│   ├── orders.php
│   └── analytics.php
├── pages/
│   ├── store.php
│   ├── product.php
│   ├── cart.php
│   └── checkout.php
├── index.php
├── database.sql
└── README.md
```

## Installation & Setup

### 1. Database Setup

1. Create a MySQL database named `ola_store`
2. Import the `database.sql` file to create all required tables
3. Update database credentials in `includes/config.php`

### 2. Web Server Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx with mod_rewrite enabled
- SSL certificate (recommended for production)

### 3. File Upload

1. Upload all files to your web server's public directory
2. Ensure proper file permissions (755 for directories, 644 for files)
3. Make sure `includes/` directory is not publicly accessible

### 4. Configuration

1. Edit `includes/config.php` with your database credentials
2. Update site URL and email settings
3. Configure payment gateway settings if needed

### 5. Admin Access

1. Default admin credentials:
   - Email: admin@olastore.com
   - Password: admin123
2. **IMPORTANT**: Change these credentials immediately after first login

## Features Overview

### Customer Features
- Browse products by category
- Advanced search and filtering
- Shopping cart with real-time updates
- Secure checkout process
- User account management
- Order tracking
- Product reviews and ratings

### Admin Features
- Product management (CRUD operations)
- Order management and tracking
- Customer management
- Analytics dashboard
- Inventory management
- Sales reports

## Security Features

- Password hashing using bcrypt
- Session-based authentication
- SQL injection prevention
- XSS protection
- CSRF token validation
- Secure file upload handling

## Performance Optimization

- Optimized database queries
- Image compression and optimization
- CSS/JS minification
- Browser caching headers
- Lazy loading for images

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Deployment Checklist

- [ ] Database created and configured
- [ ] File permissions set correctly
- [ ] SSL certificate installed
- [ ] Admin credentials changed
- [ ] Payment gateway configured
- [ ] Email settings configured
- [ ] Backup system in place
- [ ] Monitoring and logging enabled

## Support

For technical support or questions, please refer to the code comments or contact the development team.

## License

This project is proprietary software. All rights reserved.

---

**Note**: This is a production-ready e-commerce solution. Ensure proper testing in a staging environment before deploying to production.