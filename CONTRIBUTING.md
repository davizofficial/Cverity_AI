# 🤝 Contributing to CVerity AI

Terima kasih atas minat Anda untuk berkontribusi pada CVerity AI! Kami menyambut kontribusi dari siapa saja.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Pull Request Process](#pull-request-process)
- [Coding Standards](#coding-standards)
- [Commit Messages](#commit-messages)

## 📜 Code of Conduct

Proyek ini mengikuti [Contributor Covenant Code of Conduct](https://www.contributor-covenant.org/). Dengan berpartisipasi, Anda diharapkan untuk menjunjung tinggi kode etik ini.

## 🎯 How Can I Contribute?

### Reporting Bugs

Jika Anda menemukan bug, silakan buat issue dengan informasi berikut:
- Deskripsi bug yang jelas
- Langkah-langkah untuk reproduce
- Expected behavior vs actual behavior
- Screenshots (jika applicable)
- Environment (browser, OS, PHP version, dll)

### Suggesting Enhancements

Kami menerima saran untuk fitur baru atau improvement. Buat issue dengan label `enhancement` dan jelaskan:
- Use case yang jelas
- Benefit untuk users
- Possible implementation (optional)

### Pull Requests

1. Fork repository
2. Buat branch baru (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 🛠️ Development Setup

### Prerequisites

- PHP 7.4+
- Composer
- Node.js (untuk development tools)
- Git

### Setup

```bash
# Clone repository
git clone https://github.com/username/cverity-ai.git
cd cverity-ai

# Setup Backend
cd Backend
composer install
cp .env.php.example .env.php
# Edit .env.php dan isi API keys

# Setup Frontend
cd ../Frontend
# No build step required (static HTML)

# Test
cd ../Backend
php -S localhost:8080
```

## 🔄 Pull Request Process

1. **Update Documentation** - Jika ada perubahan API atau fitur baru
2. **Test Your Changes** - Pastikan tidak ada breaking changes
3. **Follow Coding Standards** - Lihat section di bawah
4. **Write Clear Commit Messages** - Lihat section di bawah
5. **Update CHANGELOG.md** - Jika applicable
6. **Request Review** - Tag maintainer untuk review

### PR Checklist

- [ ] Code follows project coding standards
- [ ] Tests pass (if applicable)
- [ ] Documentation updated
- [ ] No breaking changes (or clearly documented)
- [ ] Commit messages are clear
- [ ] Branch is up-to-date with main

## 📝 Coding Standards

### PHP

```php
<?php
// Use PSR-12 coding standard
// https://www.php-fig.org/psr/psr-12/

class ExampleClass {
    private $property;
    
    public function exampleMethod($param) {
        // Use camelCase for methods
        // Use snake_case for variables (if needed)
        $local_var = $this->property;
        
        return $local_var;
    }
}
```

### JavaScript

```javascript
// Use ES6+ features
// Use camelCase for variables and functions
// Use PascalCase for classes

const exampleFunction = (param) => {
    // Clear and descriptive names
    const result = processData(param);
    return result;
};

class ExampleClass {
    constructor() {
        this.property = null;
    }
}
```

### HTML

```html
<!-- Use semantic HTML5 -->
<!-- Proper indentation (2 or 4 spaces) -->
<!-- Descriptive class names -->

<section class="hero-section">
    <h1 class="hero-title">Title</h1>
    <p class="hero-description">Description</p>
</section>
```

## 💬 Commit Messages

Gunakan format conventional commits:

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc)
- `refactor`: Code refactoring
- `test`: Adding tests
- `chore`: Maintenance tasks

### Examples

```bash
feat(frontend): add dark mode toggle

Add dark mode toggle button in header.
Users can now switch between light and dark themes.

Closes #123
```

```bash
fix(backend): resolve CORS issue with Vercel

Update CORS headers to allow requests from Vercel domain.
Fixes "Access-Control-Allow-Origin" error.

Fixes #456
```

## 🧪 Testing

### Backend Testing

```bash
cd Backend
composer test
```

### Frontend Testing

```bash
cd Frontend
# Open in browser and test manually
# Or use automated testing tools
```

## 📚 Documentation

Jika Anda menambahkan fitur baru atau mengubah API:

1. Update `README.md` jika perlu
2. Update `Backend/README.md` untuk API changes
3. Update `Frontend/README.md` untuk UI changes
4. Add comments di code untuk complex logic

## 🎨 Design Guidelines

### UI/UX

- Follow existing design patterns
- Maintain consistency with current UI
- Ensure responsive design (mobile, tablet, desktop)
- Use Tailwind CSS classes
- Follow accessibility guidelines (WCAG 2.1)

### Colors

- Primary: `#7C3AED` (Purple)
- Primary Dark: `#6D28D9`
- Success: `#10B981` (Green)
- Warning: `#FF9500` (Orange)
- Error: `#EF4444` (Red)

## 🔒 Security

Jika Anda menemukan security vulnerability:

1. **JANGAN** buat public issue
2. Email ke: security@cverity.ai
3. Berikan detail vulnerability
4. Tunggu response dari maintainer

## 📞 Questions?

Jika ada pertanyaan:
- Buka issue dengan label `question`
- Email: support@cverity.ai
- Join Discord: [link]

## 🙏 Thank You!

Terima kasih atas kontribusi Anda! Setiap kontribusi, sekecil apapun, sangat berarti untuk proyek ini.

---

Happy Contributing! 🚀
