# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|

  # config.vm.box = "ubuntu/trusty64"
  config.vm.box = "e45456/stat"

  # config.vm.network "forwarded_port", guest: 80, host: 8080

  config.vm.network "private_network", ip: "192.168.50.101"

  config.vm.synced_folder ".", "/vagrant"

  config.vm.provision "shell", inline: <<-SHELL
     cd /vagrant/install
     sudo ./install.sh
     cd -
  SHELL
end
