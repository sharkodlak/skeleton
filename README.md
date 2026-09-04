# Skeleton for PHP projects

This repository is intentionally kept framework-agnostic on the main branch.
The shared project structure, conventions, and base configuration live here.
Framework-specific implementations are maintained in dedicated branches created from main.
Use main as the baseline when creating a new framework-specific branch.

## Getting started

```bash
make install
```

`make install` copies `.env.example` to `.env` (only when `.env` does not exist yet)
and starts the containers. `.env` is not versioned — keep local secrets there and
mirror every new variable back into `.env.example`.
