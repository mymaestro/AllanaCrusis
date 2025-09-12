# Token email
We're adding a column to the download_tokens table to track who's getting tokens.
```sql
ALTER TABLE download_tokens ADD COLUMN email VARCHAR(255) DEFAULT NULL COMMENT 'The address to which the token was sent.' AFTER token;
```