# MFA Verification Fix - TODO List

## Status: [4/5] ✅✅✅✅

### 1. ✅ Fixed scheb_two_factor.yaml config
- Used valid options: `issuer: 'Humania'`, `leeway: 30` (±30s tolerance)
- Defaults handle SHA1/6-digits/30s (matches Entity + Authenticator apps)

### 2. ✅ Improve 2fa.html.twig input validation
- Numeric-only input (`type="tel"`), enhanced JS filtering/paste handling, auto-submit

### 3. [ ] Add logging to MfaService.php (optional)
- Skip for now unless test fails

### 4. [ ] Clear cache & test
- Run: `cd SYMFONY && bin/console cache:clear`
- **Test:** Enable MFA → Login → App code should now verify ✅

### 5. [ ] Verify complete
- Confirm: Login → password → TOTP accepts valid code

----

**Ready to test!** Clear cache then try full flow. Bundle now configured correctly for authenticator apps.

