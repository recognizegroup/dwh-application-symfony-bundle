# Recognize DWH Application Bundle
## Installation
### Security
Add the following in `config/packages/security.yaml`:
User provider:
```yaml
providers:
    dwhsecurity:
        id: Recognize\DwhApplication\Security\DwhUserProvider
```
User encoder (currently only bcrypt supported):
```yaml
encoders:
    Recognize\DwhApplication\Model\DwhUser: bcrypt
```
Firewall for the DWH-bridge:
```yaml
firewalls:
    recognize_dhw:
        pattern: ^/api/dwh
        http_basic:
            realm: Recognize DWH
            provider: dwhsecurity
        anonymous: false
        stateless: true
```
Ensure authentication for DWH-API paths:
```yaml
access_control:
    - { path: ^/api/dwh, roles: ROLE_DWH_BRIDGE }
```

### Configuration
The encrypted token requires a token that is encrypted with the specified encryption.
```yaml
recognize_dwh:
      path: /api/dwh
      encryption: bcrypt
      encrypted_token: $2y$12$ADbwlXKfMjsHKayFlBSuLuu02FkrtgzdNWfCOrzWrCR8zkSoNsUfG
```
