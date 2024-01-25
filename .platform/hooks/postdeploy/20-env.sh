#!/usr/bin/bash
/opt/elasticbeanstalk/bin/get-config environment > /tmp/env.json
echo "<?php" > /tmp/aws-env.config.php
echo "define(\"RDS_DB_NAME\", " >> /tmp/aws-env.config.php && cat /tmp/env.json | jq '.RDS_DB_NAME' >> /tmp/aws-env.config.php && echo ");" >> /tmp/aws-env.config.php
echo "define(\"RDS_HOSTNAME\", " >> /tmp/aws-env.config.php && cat /tmp/env.json | jq '.RDS_HOSTNAME' >> /tmp/aws-env.config.php && echo ");" >> /tmp/aws-env.config.php
echo "define(\"RDS_USERNAME\", " >> /tmp/aws-env.config.php && cat /tmp/env.json | jq '.RDS_USERNAME' >> /tmp/aws-env.config.php && echo ");" >> /tmp/aws-env.config.php
echo "define(\"RDS_PASSWORD\", " >> /tmp/aws-env.config.php && cat /tmp/env.json | jq '.RDS_PASSWORD' >> /tmp/aws-env.config.php && echo ");" >> /tmp/aws-env.config.php
echo "define(\"RDS_PORT\", " >> /tmp/aws-env.config.php && cat /tmp/env.json | jq '.RDS_PORT' >> /tmp/aws-env.config.php && echo ");" >> /tmp/aws-env.config.php
echo "define(\"SMTP_HOSTNAME\", " >> /tmp/aws-env.config.php && cat /tmp/env.json | jq '.SMTP_HOSTNAME' >> /tmp/aws-env.config.php && echo ");" >> /tmp/aws-env.config.php
echo "define(\"SMTP_SSL\", " >> /tmp/aws-env.config.php && cat /tmp/env.json | jq '.SMTP_SSL' >> /tmp/aws-env.config.php && echo ");" >> /tmp/aws-env.config.php
echo "define(\"SMTP_PORT\", " >> /tmp/aws-env.config.php && cat /tmp/env.json | jq '.SMTP_PORT' >> /tmp/aws-env.config.php && echo ");" >> /tmp/aws-env.config.php
echo "define(\"SMTP_USERNAME\", " >> /tmp/aws-env.config.php && cat /tmp/env.json | jq '.SMTP_USERNAME' >> /tmp/aws-env.config.php && echo ");" >> /tmp/aws-env.config.php
echo "define(\"SMTP_PASSWORD\", " >> /tmp/aws-env.config.php && cat /tmp/env.json | jq '.SMTP_PASSWORD' >> /tmp/aws-env.config.php && echo ");" >> /tmp/aws-env.config.php
mv /tmp/aws-env.config.php /var/app/current/config
rm /tmp/env.json