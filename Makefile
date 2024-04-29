# Makefile pour automatiser les tâches Docker

# Variables
IMAGE_NAME := jimmycreatis/frankensf
TAG := latest

# Construire l'image Docker
build:
	@docker build -t $(IMAGE_NAME):$(TAG) .

# Pusher l'image sur Docker Hub
push:
	@docker push $(IMAGE_NAME):$(TAG)

# Puller l'image depuis Docker Hub
pull:
	@docker pull $(IMAGE_NAME):$(TAG)

# Commande par défaut
.PHONY: build push pull