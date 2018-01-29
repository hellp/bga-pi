#!/bin/bash
find . | entr lftp sftp://fneumann:$BGA_SFTP_PASSWORD@1.studio.boardgamearena.com -e "mirror -Rv -x .git . pi/; bye"
