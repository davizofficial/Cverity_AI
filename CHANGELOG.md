# Changelog

All notable changes to CVerity AI will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned Features
- User authentication & account system
- CV history & version tracking
- Real-time job API integration (LinkedIn, Indeed)
- AI-powered CV builder
- Interview preparation assistant
- Email notifications for job matches
- Mobile app (React Native)

## [1.0.0] - 2024-12-04

### Added - Project Reorganization

#### 🏗️ Structure
- Separated Frontend and Backend into distinct folders
- Frontend optimized for Vercel deployment
- Backend optimized for PHP hosting deployment

#### 📖 Documentation
- `README.md` - Main project documentation
- `QUICK_START.md` - 15-minute setup guide
- `DEPLOYMENT_GUIDE.md` - Comprehensive deployment instructions
- `MOVE_FILES_GUIDE.md` - File migration guide
- `FILES_CREATED.md` - List of all new files
- `ARCHITECTURE.md` - System architecture documentation
- `CONTRIBUTING.md` - Contribution guidelines
- `SUMMARY.md` - Project reorganization summary
- `CHANGELOG.md` - This file
- `Frontend/README.md` - Frontend-specific documentation
- `Backend/README.md` - Backend-specific documentation

#### ⚙️ Configuration Files
- `Frontend/vercel.json` - Vercel deployment configuration
- `Frontend/assets/js/config.js` - API endpoint configuration
- `Backend/.env.php.example` - Environment configuration template
- `Backend/.htaccess` - Apache web server configuration
- `Backend/app/.htaccess` - App directory protection
- `Backend/uploads/.htaccess` - Uploads directory protection
- `.gitignore` - Git ignore rules
- `.gitattributes` - Git attributes for line endings
- `.github/workflows/deploy.yml` - GitHub Actions CI/CD

#### 🛠️ Utility Scripts
- `move-files.ps1` - PowerShell migration script (Windows)
- `move-files.sh` - Bash migration script (Linux/Mac)

#### 📄 Legal
- `LICENSE` - MIT License

#### 🎨 Frontend
- `Frontend/index.html` - Converted from index.php
- Updated JavaScript to work with separated backend
- Configured CORS for cross-origin requests

#### 🖥️ Backend
- Organized API endpoints in `app/` directory
- Organized core libraries in `lib/` directory
- Added `.gitkeep` files for empty directories
- Configured CORS headers for Vercel frontend

### Changed

#### 🔄 Architecture
- Migrated from monolithic to separated architecture
- Frontend now deploys to Vercel (serverless)
- Backend remains on traditional PHP hosting
- Improved scalability and maintainability

#### 📡 API
- Updated CORS configuration for cross-origin support
- Improved error handling and logging
- Better session management

#### 🔒 Security
- Enhanced file upload security
- Improved .htaccess protection
- Better environment variable management
- Added security headers

### Fixed
- CORS issues with cross-origin requests
- File upload path issues
- Session management across domains
- Environment configuration loading

### Deprecated
- Monolithic structure (old structure still available in separate branch)

### Removed
- None (backward compatible)

### Security
- Added CORS whitelist configuration
- Improved file upload validation
- Enhanced .htaccess security rules
- Better error message handling (no sensitive info leak)

## [0.9.0] - 2024-11-XX (Pre-reorganization)

### Added
- Initial CV analysis with Gemini AI
- PDF and DOCX file support
- ATS score calculation
- Job matching with LinkedIn data
- Improved CV generation
- DOCX download functionality

### Features
- CV upload and parsing
- AI-powered CV evaluation
- Job recommendations
- Gap analysis
- Actionable suggestions

---

## Version History

- **1.0.0** - Project reorganization (Frontend/Backend separation)
- **0.9.0** - Initial release (monolithic structure)

## Migration Guide

### From 0.9.0 to 1.0.0

If you're upgrading from the old monolithic structure:

1. **Backup your data:**
   ```bash
   cp -r uploads/ uploads_backup/
   cp -r cv_data/ cv_data_backup/
   cp .env.php .env.php.backup
   ```

2. **Run migration script:**
   ```bash
   # Windows
   powershell -ExecutionPolicy Bypass -File move-files.ps1
   
   # Linux/Mac
   chmod +x move-files.sh
   ./move-files.sh
   ```

3. **Update configuration:**
   - Frontend: Update `assets/js/config.js` with backend URL
   - Backend: Update CORS headers with frontend URL

4. **Deploy:**
   - Deploy Frontend to Vercel
   - Deploy Backend to hosting
   - Test integration

See [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) for detailed instructions.

## Support

For questions about changes or upgrades:
- GitHub Issues: [Report Issue](https://github.com/username/cverity-ai/issues)
- Email: support@cverity.ai

---

**Note:** This changelog follows [Keep a Changelog](https://keepachangelog.com/) format.
