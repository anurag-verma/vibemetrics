# VibeMetrics Deployment Guides

> **Full documentation:** [docs/guides/README.md](../guides/README.md)

Choose the guide that matches your hosting:

| Hosting | Guide | Deploy workflow |
|---------|--------|-----------------|
| **Hostinger shared** (Business, etc.) | [HOSTINGER-SHARED-CICD.md](./HOSTINGER-SHARED-CICD.md) | `.github/workflows/deploy-hostinger.yml` |
| **VPS / Cloud** (DigitalOcean, Hetzner, AWS, etc.) | [VPS-CLOUD-CICD.md](./VPS-CLOUD-CICD.md) | `.github/workflows/deploy-vps.yml` |

## Quick start

1. Add GitHub secrets: `SSH_HOST`, `SSH_USER`, `SSH_PORT`, `SSH_KEY`, `DEPLOY_PATH`
2. Push to `main` — **CI** runs tests automatically
3. Use **one** deploy workflow only:
   - Hostinger → keep `deploy-hostinger.yml`, delete or disable `deploy-vps.yml`
   - VPS → keep `deploy-vps.yml`, delete or disable `deploy-hostinger.yml`  
   (Both trigger on push to `main`; running both would deploy twice.)

## Scripts

| Script | Use on |
|--------|--------|
| `scripts/deploy-hostinger.sh` | Manual deploy on shared hosting |
| `scripts/deploy-vps.sh` | Manual deploy on VPS |

## Export to Word or PDF

From the project root (requires [Pandoc](https://pandoc.org/)):

```bash
pandoc docs/deployment/HOSTINGER-SHARED-CICD.md -o docs/deployment/HOSTINGER-SHARED-CICD.docx
pandoc docs/deployment/VPS-CLOUD-CICD.md -o docs/deployment/VPS-CLOUD-CICD.docx
```

Open the `.docx` in Word and use **File → Save As → PDF** for PDF versions.

Without Pandoc: open the `.md` files in Word or VS Code and export/print to PDF.
