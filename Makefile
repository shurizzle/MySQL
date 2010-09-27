PHPDOC ?= $(shell which phpdoc)
SOURCES = src/MySQL.class.php
TEMPLATE ?= HTML:frames:earthli
DOC_DIR ?= docs
TARGET = MySQL

all: makedoc

makedoc: $(SOURCES)
	@echo "[+] Making docs..."
	@$(PHPDOC) -o $(TEMPLATE) -f $(SOURCES) -t $(DOC_DIR) -ti $(TARGET) &>/dev/null

clean:
	@echo "[+] Cleaning..."
	@$(shell rm -Rf $(DOC_DIR))

.PHONY: all makedoc clean
