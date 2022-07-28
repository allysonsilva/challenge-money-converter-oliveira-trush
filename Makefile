-include .envrc
export

uname_OS := $(shell uname -s)
user_UID := $(shell id -u)
user_GID := $(shell id -g)
current_uid ?= "${user_UID}:${user_GID}"

ifeq ($(uname_OS),Darwin)
	user_UID := 1001
	user_GID := 1001
endif

# Passing the >_ options option
options := $(if $(options),$(options),--env-file ./docker/.env)
# Passing the >_ up_options option
up_options := $(if $(up_options),$(up_options),--force-recreate --no-build --no-deps --detach)
# Passing the >_ common_options option
common_options := $(if $(common_options),$(common_options),--compatibility --ansi=auto)

# ==================================================================================== #
# HELPERS
# ==================================================================================== #

# internal functions
define message_failure
	"\033[1;31m ❌$(1)\033[0m"
endef

define message_success
	"\033[1;32m ✅$(1)\033[0m"
endef

define message_info
	"\033[0;34m❕$(1)\033[0m"
endef

## help: print this help message
.PHONY: help
help:
	@echo 'Usage:'
	@sed -n 's/^##//p' ${MAKEFILE_LIST} | column -t -s ':' |  sed -e 's/^/ /'

# ==================================================================================== #
# DOCKER
# ==================================================================================== #

.PHONY: docker/config-env
docker/config-env:
	@cd docker && cp -n .env.compose .env || true
	@cd docker && sed -i "/^# PWD/c\PWD=$(shell pwd)" .env
	@cd docker && sed -i "/^# ROOT_PATH/c\ROOT_PATH=$(shell pwd)" .env
	@cd docker && sed -i "/# USER_UID=.*/c\USER_UID=$(user_UID)" .env
	@cd docker && sed -i "/# USER_GID=.*/c\USER_GID=$(user_GID)" .env
	@cd docker && sed -i "/^# CURRENT_UID/c\CURRENT_UID=${current_uid}" .env
	@echo
	@echo $(call message_success, Run \`make docker/config-env\` successfully executed)

.PHONY: docker/healthcheck
docker/healthcheck:
# make docker/healthcheck container=app_database
	@timeout=60; counter=0; \
	printf "\n\033[3;33mEsperando healthcheck do container de banco de dados \"$${container}\" = \"healthy\" ⏳ \033[0m\n" ; \
	until [[ "$$(docker container inspect -f '{{.State.Health.Status}}' $${container})" == "healthy" ]] ; do \
		printf '.' ; \
		if [[ $${timeout} -lt $${counter} ]]; then \
			printf "\n\033[1;31mERROR: Timed out waiting for \"$${container}\" to come up/healthy ❌\033[0m\n\n" ; \
			exit 1 ; \
		fi ; \
		\
		printf "\n\033[35mWaiting for \"$${container}\" to be ready/healthy ($${counter}/$${timeout}) ⏱ \033[0m\n" ; \
		sleep 5s; counter=$$((counter + 5)) ; \
	done
	@echo
	@echo $(call message_success, HEALTHCHECK do Container \"$${container}\" OK)

.PHONY: docker/build/app
docker/build/app:
	@echo
	@echo $(call message_info, Build an image APP... 🏗)
	@echo
	docker-compose \
		-f ./docker/php/services/app/docker-compose.yml \
		--ansi=auto \
		--env-file ./docker/.env \
		build --progress=plain app

.PHONY: docker/app/up
docker/app/up:
	@echo
	@echo $(call message_info, Running APP Container... 🚀)
	@echo
	@$(MAKE) --no-print-directory docker/service/up context="php/services/app" up_options="--force-recreate --no-deps"

.PHONY: docker/queue/up
docker/queue/up:
	@echo
	@echo $(call message_info, Running QUEUE Container... 🚀)
	@echo
	@$(MAKE) --no-print-directory docker/service/up context="php/services/queue"

.PHONY: docker/scheduler/up
docker/scheduler/up:
	@echo
	@echo $(call message_info, Running SCHEDULER Container... 🚀)
	@echo
	@$(MAKE) --no-print-directory docker/service/up context="php/services/scheduler"

.PHONY: docker/database/up
docker/database/up:
	@echo
	@echo $(call message_info, Running Docker Database... 🚀)
	@echo
	@$(MAKE) --no-print-directory docker/service/up context=mysql

.PHONY: docker/redis/up
docker/redis/up:
	@echo
	@echo $(call message_info, Running Docker Redis... 🚀)
	@echo
	@$(MAKE) --no-print-directory docker/service/up context=redis

# make  docker/service/up \
		context=FOLDER_IN_SERVICES \
		options=--verbose \
		up_options="--force-recreate" \
		services="services_in_docker_compose"
.PHONY: docker/service/up
docker/service/up:
	@docker-compose --log-level=ERROR -f ./docker/$(context)/docker-compose.yml \
		$(options) $(common_options) \
		up $(up_options) \
		$(if $(services),$(services),)

.PHONY: docker/up
docker/up:
	@echo
	@echo $(call message_info, Running Docker Application... 🚀)
	@echo
	@docker-compose --log-level=ERROR -f ./docker/docker-compose.yml up
	@$(MAKE) --no-print-directory docker/database/up
	@$(MAKE) --no-print-directory docker/healthcheck container=$(if $(database_container),$(database_container),currency_database)
	@$(MAKE) --no-print-directory docker/redis/up
	@$(MAKE) --no-print-directory docker/healthcheck container=$(if $(redis_container),$(redis_container),currency_redis)
	@$(MAKE) --no-print-directory docker/app/up
