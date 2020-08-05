FROM php:7.3-fpm

ENV LANG C.UTF-8
ARG USERNAME=vscode
ARG USER_UID=1000
ARG USER_GID=$USER_UID

# 扩展配置
COPY conf.d/ $PHP_INI_DIR/conf.d/

# 配置 zsh
COPY .oh-my-zsh/ /home/${USERNAME}/.oh-my-zsh
COPY .oh-my-zsh/templates/zshrc.zsh-template /home/${USERNAME}/.zshrc

# 构建PHP
RUN sed -ri 's/https?:\/\/.*\.debian\.org/https:\/\/mirrors.tuna.tsinghua.edu.cn/g' /etc/apt/sources.list \
    && apt-get update \
    && apt-get -y install --no-install-recommends apt-utils dialog 2>&1 \
    && apt-get -y install git procps iproute2 lsb-release curl wget \
        vim zsh zip libzip-dev mlocate lsof net-tools libfreetype6-dev \
        libjpeg62-turbo-dev libpng-dev make autoconf gcc g++ libpcre3-dev \
        libpcre3 openssl libssl-dev sudo jq gnupg2 ca-certificates \
    #
    # Install xdebug redis mysqli pdo_mysql bcmath gd zip opcache sockets
    && pecl install redis xdebug \
	&& docker-php-ext-enable redis xdebug \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
	&& docker-php-ext-install -j$(nproc) gd mysqli pdo_mysql bcmath zip opcache sockets pcntl \
    #
    # 处理用户权限
    && groupadd --gid $USER_GID $USERNAME \
    && useradd -s /bin/zsh --uid $USER_UID --gid $USER_GID -m $USERNAME \
    && echo $USERNAME ALL=\(root\) NOPASSWD:ALL > /etc/sudoers.d/$USERNAME \
    && chmod 0440 /etc/sudoers.d/$USERNAME \
    && chown -R ${USERNAME}:${USERNAME} /home/${USERNAME} \
    #
    # Composer
    && sudo -u ${USERNAME} mkdir -p /home/${USERNAME}/bin \
    && sudo -u ${USERNAME} curl -o /home/${USERNAME}/bin/composer https://mirrors.aliyun.com/composer/composer.phar \
    && chmod +x /home/${USERNAME}/bin/composer \
    && sudo -u ${USERNAME} /home/${USERNAME}/bin/composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/ \
    && sudo -u ${USERNAME} echo "export PATH=\$PATH:\$HOME/.composer/vendor/bin" >> /home/${USERNAME}/.envrc \
    #
    # 环境变量配置
    && echo "export USER_BIN=/home/${USERNAME}/bin" >> /home/${USERNAME}/.envrc \
    && echo "export PATH=\$USER_BIN:\$PATH " >> /home/${USERNAME}/.envrc \
    && echo -e "\n. ~/.envrc" >> /home/${USERNAME}/.zshrc 

# Install Nginx、Nodejs And yarn
RUN echo "deb http://nginx.org/packages/debian `lsb_release -cs` nginx" | tee /etc/apt/sources.list.d/nginx.list \
    && curl -fsSL https://nginx.org/keys/nginx_signing.key | apt-key add - \
    && curl -sL https://deb.nodesource.com/setup_14.x | bash - \
    && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
    && echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list \
    && apt-get update \
    && apt-get install -y nginx nodejs yarn \
    #
    # 修改 Nginx 配置
    && sed -ri "s/user.*nginx/user ${USERNAME}/g" /etc/nginx/nginx.conf \
    #
    # 清理包缓存信息
    && apt-get autoremove -y \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/*
