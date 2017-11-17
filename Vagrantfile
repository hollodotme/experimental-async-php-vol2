Vagrant.configure("2") do |config|

	config.vm.box = "DreiWolt/devops007"
	config.vm.network "private_network", ip: "192.168.3.12"
	config.vm.hostname = "ExAsyncPHP2"
	config.vm.provision "shell", path: "resources/vagrant-bootstrap.sh"

end
