# Testing

### How to run all unit tests
```
php bin/phpunit
```

### How to reset test database
Current test suite utilizes [DoctrineTestBundle](https://github.com/dmaicher/doctrine-test-bundle) so database changes between different tests are not committed
```
./bin/reset-test.sh
```

### How to test local emails
Docker compose requires image of MailHog and makes it available at [http://localhost:9201/](http://localhost:9201/)
UI shows all messages sent by the system

### Test users with customer roles
 - user0/user0
 - user1/user1
 - user2/user2
 - user3/user3
 - user4/user4
 - user5/user5
 - user6/user6
 - user7/user7
 - user8/user8
 - user9/user9
 - user10/user10
 - user11/user11
 - user12/user12
 - user13/user13
 - user14/user14
 - user15/user15
 - user16/user16
 - user17/user17
 - user18/user18
 - user19/user19
 - user20/user20

### Test users without customer role - use them to test checkout flow after login
 - user21/user21
 - user22/user22
 - user23/user23
 - user24/user24
 - user25/user25

### SPA Routing
- All URls should have a `/user` prefix. All of them require authenticated user, login before testing
- All URLs with `/user` prefix will be redirected to the SPA by Symfony
- `/user/dashboard` - Main Dashboard URL
- `/user/asset/{slug}` - Asset type dashboard (see table asset_type in the database and `10 - Assets Group - RE + Alts` screen). Examples:
  - `/user/asset/real-estate`
  - `/user/asset/public-non-traded-reit`
- `/user/asset/{slug}/add` - Add investment URL to the selected asset type `07 - Add investment - RE + Alts` screen. Examples:
  - `/user/asset/real-estate/add`
  - `/user/asset/public-non-traded-reit/add`
- `/user/position/{uuid}` - Position dashboard: `11 - Asset page - RE + Alts` screen
- `/user/position/{uuid}/edit` - Edit position
- `/user/asset/{slug}/edit` - Edit asset
- `/user/sponsors` - List of sponsors: `09 - Sponsors` screen
- `/user/transactions` - List of transactions: `12 - Transactions` screen
- `/user/tags` - List of tags: `13 - Tags` screen
- `/user/profile` - Update user profile
- `/user/avatar` - Update user avatar
- `/user/password` - Update user password

### Use cases
1. No assets and liabilities: `user0`
2. REIT with dividends: `user1`
3. REIT with dividends reinvestment: `user2`
4. Always with liabilities: `user4`
5. Negative net value: `user19`
6. Private sponsors: `user1` and `user2`
7. Completed investments: `user9`, `user8`, `user7`, `user6`
8. Big property owner: `user8`
9. Property owners: `user2`, `user3`, `user4`, `user8`