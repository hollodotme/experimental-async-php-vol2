#!/usr/bin/env bash

apt-get update
apt-get install -y rabbitmq-server

composer self-update
chmod -R 0777 /home/vagrant/.composer
mkdir -pm 0777 /var/log/php/

ln -sf /vagrant/resources/commands.conf /etc/php/7.1/fpm/pool.d/commands.conf

service php7.1-fpm restart
