#!/usr/bin/env bash
set -euo pipefail

KEY=~/.ssh/LightsailDefaultKey-us-east-1.pem
USER=ubuntu

case "${1:-}" in
    sum) HOST="34.203.50.164" ;;
    *)
        echo "Usage: $0 <code>"
        echo ""
        echo "Available servers:"
        echo "  sum"
        exit 1
        ;;
esac

ssh -i "$KEY" "$USER@$HOST"
