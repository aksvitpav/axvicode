CERTS_DIR=docker/traefik/certs
DOMAINS=axvicode.test mail.axvicode.test

generate-certs:
	mkcert -install
	mkdir -p $(CERTS_DIR)
	mkcert -cert-file $(CERTS_DIR)/axvicode.test.crt \
	       -key-file $(CERTS_DIR)/axvicode.test.key \
	       $(DOMAINS)

clean-certs:
	rm -rf $(CERTS_DIR)

pint:
	./vendor/bin/pint
