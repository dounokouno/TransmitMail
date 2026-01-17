ARG PHP_VERSION=8.5-cli
FROM php:${PHP_VERSION}

# Debian 10 (Buster) 以前の古いディストリビューションの場合、リポジトリがアーカイブされているため、パッケージの取得先を archive.debian.org に変更します。
# これにより、PHP 7.2 のような古いバージョンでもビルドが可能になります。
RUN if grep -q "buster" /etc/os-release; then \
        echo "deb http://archive.debian.org/debian/ buster main" > /etc/apt/sources.list && \
        echo "deb http://archive.debian.org/debian/ buster-updates main" >> /etc/apt/sources.list && \
        echo "deb http://archive.debian.org/debian-security/ buster/updates main" >> /etc/apt/sources.list; \
    fi

# Install essential packages for apt to work correctly
RUN apt-get update -y && \
    apt-get install -y --no-install-recommends \
    ca-certificates \
    debian-archive-keyring \
    gnupg

# Update package lists again after ensuring keyring and certs are up-to-date
RUN apt-get update -y

# Add amd64 architecture and install essential libraries for cross-architecture compatibility (e.g., running amd64 Chrome on arm64 host with Rosetta)
RUN dpkg --add-architecture amd64 && \
    apt-get update -y && \
    apt-get install -y libc6:amd64

# Install PHP extensions dependencies and Chrome dependencies
RUN apt-get install -y --no-install-recommends \
    unzip \
    zip \
    libzip-dev \
    libicu-dev \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    wget \
    libonig-dev \
    # Add additional libraries that Chrome may depend on
    libnss3-dev:amd64 \
    default-libmysqlclient-dev \
    libglib2.0-0:amd64 \
    libnss3:amd64 \
    libdbus-1-3:amd64 \
    libatk1.0-0:amd64 \
    libatk-bridge2.0-0:amd64 \
    libatspi2.0-0:amd64 \
    libcups2:amd64 \
    libudev1:amd64 \
    libxkbcommon0:amd64 \
    libcairo2:amd64 \
    libpango-1.0-0:amd64 \
    libfontconfig1:amd64 \
    libx11-xcb1:amd64 \
    libxcomposite1:amd64 \
    libxcursor1:amd64 \
    libxdamage1:amd64 \
    libxext6:amd64 \
    libxfixes3:amd64 \
    libxi6:amd64 \
    libxrandr2:amd64 \
    libxrender1:amd64 \
    libxss1:amd64 \
    libxtst6:amd64 \
    libgbm1:amd64 \
    libgbm-dev:amd64 \
    libxshmfence-dev:amd64 \
    libasound2:amd64 \
    lsb-release \
    xdg-utils \
    fonts-liberation \
    fonts-ipafont-gothic \
    && rm -rf /var/lib/apt/lists/*

# Install libu2f-udev only on older Debian versions (like Buster) where it's available and needed.
RUN if grep -q "buster" /etc/os-release; then \
        apt-get update -y && apt-get install -y --no-install-recommends libu2f-udev:amd64 && rm -rf /var/lib/apt/lists/*; \
    fi

# TODO: このパラメーターが必要か検討する
ARG CHROME_VERSION=""
# Chrome for Testing と ChromeDriver の特定のバージョンをインストール
# Install specific versions of Chrome for Testing and ChromeDriver.
# Specify the version to match the Session info from the test logs (e.g., chrome=137.0.7151.68).
# Please verify that this version exists in known-good-versions-with-downloads.json.
# If it doesn't exist, please adjust to the nearest available version.

# jq をインストール
RUN set -ex; \
    JQ_VERSION="1.7.1"; \
    JQ_URL="https://github.com/jqlang/jq/releases/download/jq-${JQ_VERSION}/jq-linux-amd64"; \
    wget -qO /usr/local/bin/jq "${JQ_URL}"; \
    chmod +x /usr/local/bin/jq

# Chrome for Testing と ChromeDriver の URL を取得し、ダウンロード
# URL を取得して一時ファイルに保存
RUN set -ex; \
    mkdir -p /tmp/cft_download; \
    if [ -n "$CHROME_VERSION" ]; then \
        echo "Specific Chrome version requested: $CHROME_VERSION"; \
        JSON_URL="https://googlechromelabs.github.io/chrome-for-testing/known-good-versions-with-downloads.json"; \
        JSON_FILE_PATH="/tmp/versions.json"; \
        wget --progress=bar:force:noscroll --show-progress --timeout=60 --tries=3 -O "$JSON_FILE_PATH" "$JSON_URL"; \
        URLS=$(jq -r --arg ver "$CHROME_VERSION" '[.versions[] | select(.version | startswith($ver)) | select(has("downloads") and .downloads.chrome and .downloads.chromedriver)] | sort_by(.version | split(".") | map(tonumber)) | last | {chrome: .downloads.chrome[] | select(.platform=="linux64") | .url, chromedriver: .downloads.chromedriver[] | select(.platform=="linux64") | .url}' "$JSON_FILE_PATH"); \
        CHROME_URL=$(echo "$URLS" | jq -r .chrome); \
        CHROMEDRIVER_URL=$(echo "$URLS" | jq -r .chromedriver); \
        rm -f "$JSON_FILE_PATH"; \
    else \
        echo "No specific Chrome version requested. Using latest stable version."; \
        JSON_URL="https://googlechromelabs.github.io/chrome-for-testing/last-known-good-versions-with-downloads.json"; \
        URLS=$(wget -qO- "$JSON_URL"); \
        CHROME_URL=$(echo "$URLS" | jq -r '.channels.Stable.downloads.chrome[] | select(.platform=="linux64") | .url'); \
        CHROMEDRIVER_URL=$(echo "$URLS" | jq -r '.channels.Stable.downloads.chromedriver[] | select(.platform=="linux64") | .url'); \
    fi; \
    if [ -z "$CHROME_URL" ] || [ "$CHROME_URL" = "null" ]; then echo "Chrome URL for linux64 not found for version '$CHROME_VERSION'." >&2; exit 1; fi; \
    if [ -z "$CHROMEDRIVER_URL" ] || [ "$CHROMEDRIVER_URL" = "null" ]; then echo "ChromeDriver URL for linux64 not found for version '$CHROME_VERSION'." >&2; exit 1; fi; \
    echo "$CHROME_URL" > /tmp/cft_download/chrome_url.txt; \
    echo "$CHROMEDRIVER_URL" > /tmp/cft_download/chromedriver_url.txt

# ダウンロード
RUN set -ex; \
    CHROME_URL=$(cat /tmp/cft_download/chrome_url.txt); \
    CHROMEDRIVER_URL=$(cat /tmp/cft_download/chromedriver_url.txt); \
    wget -q --show-progress -O /tmp/cft_download/chrome-linux64.zip "$CHROME_URL"; \
    wget -q --show-progress -O /tmp/cft_download/chromedriver-linux64.zip "$CHROMEDRIVER_URL"

# ダウンロードしたアーカイブを展開し、実行ファイルを配置
RUN set -ex; \
    unzip /tmp/cft_download/chrome-linux64.zip -d /opt; \
    mkdir -p /tmp/chromedriver_unzip; \
    unzip -o /tmp/cft_download/chromedriver-linux64.zip -d /tmp/chromedriver_unzip; \
    find /tmp/chromedriver_unzip -name chromedriver -type f -exec mv -f {} /usr/local/bin/chromedriver \;; \
    echo /opt/chrome-linux64 > /etc/ld.so.conf.d/chrome.conf; \
    ldconfig; \
    ln -sf /opt/chrome-linux64/chrome /usr/bin/google-chrome; \
    ln -sf /opt/chrome-linux64/chrome /usr/bin/google-chrome-stable; \
    chmod +x /usr/local/bin/chromedriver; \
    echo "--- Checking Chrome and ChromeDriver versions ---"; \
    google-chrome --version --no-sandbox && \
    chromedriver --version && \
    # 一時ファイルをクリーンアップ
    rm -rf /tmp/cft_download /tmp/chromedriver_unzip

# PHP拡張機能
RUN PHP_MAJOR_MINOR_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;'); \
    if dpkg --compare-versions "$PHP_MAJOR_MINOR_VERSION" "lt" "7.4"; then \
      # PHP 7.3以前の場合のGD設定オプション
      # libpng-dev はインストール済みなので、通常はこれでPNGもサポートされるはず
      docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/; \
    else \
      # PHP 7.4以降の場合のGD設定オプション (libpng-dev があればPNGは自動的にサポートされる)
      docker-php-ext-configure gd --with-freetype --with-jpeg; \
    fi

# 各拡張機能を個別のRUN命令でインストールしてエラー箇所を特定しやすくする
# mbstring は exif より先にインストールする
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install -j$(nproc) gd
RUN docker-php-ext-install -j$(nproc) intl
RUN docker-php-ext-install -j$(nproc) zip
RUN docker-php-ext-install -j$(nproc) exif
RUN docker-php-ext-install -j$(nproc) pdo_mysql

# Create directories for Panther logs and screenshots
# Pantherのログやスクリーンショット保存用のディレクトリを作成
RUN mkdir -p /app/tmp /var/log/panther_logs && \
    chmod -R 777 /app/tmp /var/log/panther_logs

# ChromeDriverのラッパースクリプトを作成
RUN echo '#!/bin/bash' > /usr/local/bin/chromedriver_wrapper.sh && \
    echo 'WRAPPER_LOG_FILE="/tmp/chromedriver_wrapper_execution.log"' >> /usr/local/bin/chromedriver_wrapper.sh && \
    echo 'echo "Wrapper script started immediately at $(date) with args: $@" >> /app/tmp/wrapper_debug.log' >> /usr/local/bin/chromedriver_wrapper.sh && \
    echo '# Redirect subsequent output of this script (stdout and stderr) to the log file' >> /usr/local/bin/chromedriver_wrapper.sh && \
    echo 'exec > /app/tmp/wrapper_debug.log 2>&1' >> /usr/local/bin/chromedriver_wrapper.sh && \
    echo 'set -x  # Print each command before it is executed' >> /usr/local/bin/chromedriver_wrapper.sh && \
    echo 'echo "--- Chromedriver Wrapper Script Continued (Launching real chromedriver) ---"' >> /usr/local/bin/chromedriver_wrapper.sh && \
    echo 'echo "Date: $(date)"' >> /usr/local/bin/chromedriver_wrapper.sh && \
    echo 'echo "Arguments received: $@" ' >> /usr/local/bin/chromedriver_wrapper.sh && \
    echo 'echo "Current user: $(whoami)"' >> /usr/local/bin/chromedriver_wrapper.sh && \
    echo 'echo "Script path: $0"' >> /usr/local/bin/chromedriver_wrapper.sh && \
    echo 'echo "PWD: $(pwd)"' >> /usr/local/bin/chromedriver_wrapper.sh && \
    echo 'ls -l /usr/local/bin/chromedriver_wrapper.sh # Check its own permissions' >> /usr/local/bin/chromedriver_wrapper.sh && \
    echo 'ls -l /usr/local/bin/chromedriver          # Check chromedriver permissions' >> /usr/local/bin/chromedriver_wrapper.sh && \
    echo 'echo "Environment:"' >> /usr/local/bin/chromedriver_wrapper.sh && \
    # The 'env' command should be part of the script to log runtime environment variables.
    echo 'env' >> /usr/local/bin/chromedriver_wrapper.sh && \
    echo 'echo "--- Executing /usr/local/bin/chromedriver ---"' >> /usr/local/bin/chromedriver_wrapper.sh && \
    echo 'exec /usr/local/bin/chromedriver "$@"' >> /usr/local/bin/chromedriver_wrapper.sh && \
    chmod +x /usr/local/bin/chromedriver_wrapper.sh

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
