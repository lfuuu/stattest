# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|

#  config.vm.box = "ubuntu/trusty64"    # clean install
  config.vm.box = "e45456/stat"         # ready to use

  config.vm.network "forwarded_port", guest: 22, host: 2222, id: "ssh", auto_correct: true
  config.vm.network "forwarded_port", guest: 80, host: 8101, id: "http", auto_correct: true

  config.vm.network "private_network", ip: "192.168.50.101"

  config.vm.synced_folder ".", "/vagrant"

  config.vm.provider "virtualbox" do |v|
    v.memory = 1024
    v.cpus = 4
  end

  config.vm.provision "shell", inline: <<-SHELL
     cd /vagrant/install
     sudo ./install_root.sh
     cd -
     cd /vagrant
     sudo ./install/install_vagrant.sh
     cd -
  SHELL
end
