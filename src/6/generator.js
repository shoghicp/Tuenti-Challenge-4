#!/usr/bin/env node

if ((process.version.split('.')[1]|0) < 10) {
	console.log('Please, upgrade your node version to 0.10+');
	process.exit();
}

var net = require('net');
var util = require('util');
var crypto = require('crypto');

var options = {
	'port': 6969,
	'host': '54.83.207.90',
}

var dh, dh2, secret, secret2, state = 0;

var socket = net.connect(options);

socket.on('data', function(data) {
	
	raw = data.toString().trim().split(':');
	var direction;
	if(raw[0] == "CLIENT->SERVER"){
		direction = 0;
	}else if(raw[0] == "SERVER->CLIENT"){
		direction = 1;
	}else{
		return;
	}

	data = raw[1].toString().trim().split('|');
	
	if(state == 0 && direction == 1 && raw[1] == "hello!"){
		state = 1;
		socket.write(raw[1] + "\n");
	}else if(state == 0){
		socket.write(raw[1] + "\n");
	} else if (state == 1 && data[0] == 'key') {
		dh = crypto.createDiffieHellman(256);
		dh.generateKeys();
		dh2 = crypto.createDiffieHellman(data[1], 'hex');
		dh2.generateKeys();
		secret2 = dh2.computeSecret(data[2], 'hex');
		socket.write(util.format('key|%s|%s\n', dh.getPrime('hex'), dh.getPublicKey('hex')));
		state++;
	} else if (state == 2 && data[0] == 'key') {
		secret = dh.computeSecret(data[1], 'hex');
		socket.write(util.format('key|%s\n', dh2.getPublicKey('hex')));
		state++;
	} else if (state == 3 && data[0] == 'keyphrase') {
		var decipher = crypto.createDecipheriv('aes-256-ecb', secret2, '');
		var keyphrase = decipher.update(data[1], 'hex', 'utf8') + decipher.final('utf8');
		keyphrase = process.argv[2];
		var cipher = crypto.createCipheriv('aes-256-ecb', secret, '');
		var keyphrase = cipher.update(keyphrase, 'utf8', 'hex') + cipher.final('hex');
		socket.write(util.format('keyphrase|%s\n', keyphrase));
		state++;
	} else if (state == 4 && data[0] == 'result') {
		var decipher = crypto.createDecipheriv('aes-256-ecb', secret, '');
		var message = decipher.update(data[1], 'hex', 'utf8') + decipher.final('utf8');
		console.log(message);
		socket.end();
	} else {
		socket.end();
	}

});
