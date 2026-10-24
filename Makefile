# The common tasks of the project. Run "make" to see the list.
#   Dev:  make up (or make up-tunnel, with TUNNEL_TOKEN), make console c="debug:router", make test
#   Live: SPEAKER_KEY=... PUBLIC_URL=... make live (or make live-tunnel, with TUNNEL_TOKEN)

DEV  = docker compose
LIVE = docker compose -f compose.prod.yaml
EXEC = $(DEV) exec app

.DEFAULT_GOAL = help
.PHONY: help up up-tunnel down logs sh console schema fixtures test live live-tunnel live-down live-logs

help: ## Show this list
	@grep -E '^[a-z-]+:.*## ' $(MAKEFILE_LIST) | awk -F ':.*## ' '{printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

## --- Dev ---------------------------------------------------------------

up: ## Start the dev server in the background (http://localhost:8080)
	$(DEV) up --build --detach

up-tunnel: ## Start the dev server with HTTPS (needs TUNNEL_TOKEN; stop the live presentation first)
	@test -n "$(TUNNEL_TOKEN)" || { echo "Set TUNNEL_TOKEN: the token of the Cloudflare Tunnel"; exit 1; }
	$(DEV) --profile tunnel up --build --detach

down: ## Stop the dev server (and its tunnel)
	$(DEV) --profile tunnel down

logs: ## Follow the logs of the dev server (and its tunnel)
	$(DEV) --profile tunnel logs --follow

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

## --- Live presentation -------------------------------------------------

live: ## Start the live presentation on the LAN (needs SPEAKER_KEY, PUBLIC_URL)
	$(LIVE) up --build --detach

live-tunnel: ## Start the live presentation with HTTPS (also needs TUNNEL_TOKEN)
	@test -n "$(TUNNEL_TOKEN)" || { echo "Set TUNNEL_TOKEN: the token of the Cloudflare Tunnel"; exit 1; }
	$(LIVE) --profile tunnel up --build --detach

# down and logs need no key, but compose reads the whole file.
live-down: ## Stop the live presentation (the database stays in its volume)
	SPEAKER_KEY=$${SPEAKER_KEY:-unused} $(LIVE) --profile tunnel down

live-logs: ## Follow the logs of the live presentation
	SPEAKER_KEY=$${SPEAKER_KEY:-unused} $(LIVE) --profile tunnel logs --follow
