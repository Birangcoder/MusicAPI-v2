<?php
// Local-only example. Copy to secrets.php and fill real values. Do not commit secrets.php.
putenv('V2_DB_HOST=your-project.pooler.supabase.com');
putenv('V2_DB_PORT=5432');
putenv('V2_DB_NAME=postgres');
putenv('V2_DB_USER=postgres.your-project');
putenv('V2_DB_PASS=your_supabase_password');
putenv('V2_APP_URL=http://localhost:10000');
putenv('JWT_SECRET=replace_with_a_long_random_secret');
