pipeline {
    agent {
        docker {
            image 'php:8.4-cli'
            args '-u root'
        }
    }

    environment {
        COMPOSER_ALLOW_SUPERUSER = '1'
        APP_ENV = 'testing'
    }

    stages {
        stage('Setup PHP') {
            steps {
                sh '''
                    apt-get update
                    apt-get install -y git unzip zip libzip-dev libpng-dev libonig-dev libfreetype6-dev libjpeg62-turbo-dev nodejs npm
                    docker-php-ext-configure gd --with-freetype --with-jpeg
                    docker-php-ext-install gd zip mbstring pdo_mysql
                    pecl install pcov
                    docker-php-ext-enable pcov
                    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
                    php composer-setup.php --install-dir=/usr/local/bin --filename=composer
                    rm composer-setup.php
                '''
            }
        }

        stage('Install') {
            steps {
                sh '''
                    composer install --no-interaction --prefer-dist
                    cp .env.example .env
                    php artisan key:generate
                '''
            }
        }

        stage('Audit') {
            steps {
                sh 'composer audit'
            }
        }

        stage('Build Assets') {
            steps {
                sh '''
                    npm install --ignore-scripts
                    npm run build
                '''
            }
        }

        stage('Test + Coverage 100%') {
            steps {
                sh '''
                    php -d pcov.enabled=1 vendor/bin/phpunit \
                        --coverage-text=coverage.txt \
                        --coverage-clover=coverage.xml \
                        --log-junit=junit.xml
                    cat coverage.txt
                    php -r '
                        $xml = simplexml_load_file("coverage.xml");
                        $m = $xml->project->metrics;
                        $total = (int) $m["statements"] + (int) $m["methods"] + (int) $m["conditionals"];
                        $covered = (int) $m["coveredstatements"] + (int) $m["coveredmethods"] + (int) $m["coveredconditionals"];
                        if ($total === 0 || $covered < $total) {
                            fwrite(STDERR, "Coverage kurang dari 100% ($covered/$total)\n");
                            exit(1);
                        }
                        echo "Coverage 100% OK\n";
                    '
                '''
            }
        }

        stage('Package') {
            steps {
                sh 'zip -r latest-app.zip . -x "vendor/*" "node_modules/*" ".git/*" ".env"'
            }
        }
    }

    post {
        always {
            junit allowEmptyResults: true, testResults: 'junit.xml'
        }
    }
}