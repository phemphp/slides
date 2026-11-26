# The common tasks of the project. Run "make" to see the list.
#   make up, make console c="debug:router", make test

DEV  = docker compose
EXEC = $(DEV) exec app

# The public URL of the slides: the URL of a tunnel to the dev server (make tunnel). The QR code
# of the deck has it, so a phone opens the slides: make up PHONE_URL=https://example.trycloudflare.com
PHONE_URL ?=
export PHONE_URL
# The name of a Cloudflare Tunnel with a DNS route, for a URL that stays the same. Empty: a quick
# tunnel with a random URL.
TUNNEL ?=
PORT ?= 8080

.DEFAULT_GOAL = help
.PHONY: help up down logs sh console schema fixtures test restart phem-link phem-unlink tunnel

help: ## Show this list
	@grep -E '^[a-z-]+:.*## ' $(MAKEFILE_LIST) | awk -F ':.*## ' '{printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

up: ## Start the dev server in the background (http://localhost:8080)
	$(DEV) up --build --detach
ifneq ($(PHONE_URL),)
	@echo "Phone: $(PHONE_URL) (the phone button of the deck shows the QR code)"
else
	@echo "Phone: no public URL. Run \"make tunnel\", then \"make up PHONE_URL=<the URL of the tunnel>\"."
endif

restart: ## Restart the dev server, for example to load a change in the linked phem
	$(DEV) restart app

down: ## Stop the dev server
	$(DEV) down

logs: ## Follow the logs of the dev server
	$(DEV) logs --follow

sh: ## Open a shell in the dev container
	$(EXEC) bash

console: ## Run a Symfony command, for example: make console c="debug:router"
	$(EXEC) php bin/console $(c)

schema: ## Update the database schema after an entity change
	$(EXEC) php bin/console doctrine:schema:update --force

fixtures: ## Load the fixtures (empties all the tables first)
	$(EXEC) php bin/console doctrine:fixtures:load --no-interaction

test: ## Run the tests
	$(EXEC) php bin/phpunit $(c)

# The link is relative: it resolves to ../phem on the host and to /phem in the container
# (compose.override.yaml mounts it there).
phem-link: ## Link vendor/phem/phem to the local phem checkout (../phem)
	@test -d ../phem || { echo "Clone phem next to this project: ../phem"; exit 1; }
	rm -rf vendor/phem/phem && ln -s ../../../phem vendor/phem/phem
	$(DEV) up --detach --force-recreate app

# Remove the link in the container: a host rm leaves a stale link in the container's view of the mount.
phem-unlink: ## Restore the released phem version in vendor/
	$(EXEC) rm -f vendor/phem/phem
	$(EXEC) composer install --no-interaction
	$(MAKE) restart

tunnel: ## Open a public URL for the slides with cloudflared (Ctrl+C stops it)
ifneq ($(TUNNEL),)
	cloudflared tunnel --no-autoupdate run --url http://localhost:$(PORT) $(TUNNEL)
else
	cloudflared tunnel --no-autoupdate --url http://localhost:$(PORT)
endif
