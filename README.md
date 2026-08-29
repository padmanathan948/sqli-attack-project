# SQL Injection Attack — Testing & Remediation

**Prepared by:** Padmanathan
**Program:** Skillogic Internship Program

A hands-on web application security project against [DVWA](https://github.com/digininja/DVWA) (Damn Vulnerable Web Application), progressing from manual exploitation through automated tooling to a working remediation, built and tested in an isolated local Kali Linux lab.

> ⚠️ All testing in this repository was performed against a deliberately vulnerable, locally hosted lab application. None of these techniques were used against systems the author does not own or have explicit authorization to test.

---

## Methodology

The assessment followed a standard progression:

1. **Lab setup & reconnaissance** — DVWA deployed natively on Kali (Apache + MariaDB), input fields mapped, unsanitized query behavior confirmed via error responses.
2. **Manual in-band SQLi** — OR-based authentication/data bypass (`' OR '1'='1`) on the SQL Injection module; the login form was also tested and found not vulnerable to the same technique.
3. **Manual UNION-based extraction** — column count via `ORDER BY`, schema enumeration via `information_schema`, full credential dump via `UNION SELECT`.
4. **Manual blind SQLi (Boolean-based)** — true/false baseline established, data extracted via `ASCII(SUBSTRING(...))` comparisons with zero visible data or errors.
5. **Manual blind SQLi (Time-based)** — `SLEEP()`-based payloads used to infer true/false purely from response timing.
6. **Automated exploitation — SQLMap** — independently confirmed the same injection points, enumerated the schema, dumped and cracked all credentials in under two minutes; also tested Medium/High security levels (`--level=5 --risk=3`), neither of which was found injectable.
7. **Automated interception — Burp Suite** — intercepted and manually edited the raw HTTP request (Proxy + Repeater) to reproduce the same exploit at the protocol level, independent of browser form handling.
8. **Automated scanning — OWASP ZAP** — authenticated active scan against the whole application; correctly flagged the same SQLi point (CWE-89, High risk) via error-based detection, plus 14 unrelated hardening findings (missing security headers, cookie flags, server version disclosure).
9. **Out-of-Band SQLi** — documented conceptually rather than executed live, since OOB requires outbound DNS/HTTP access from the database server that this isolated lab intentionally does not have.
10. **Original vulnerable application** — a minimal PHP/MySQL login app built from scratch using raw string concatenation, to demonstrate the exact vulnerability pattern independent of DVWA.
11. **Remediation** — the same application rebuilt using a parameterized query (`mysqli_prepare` / `bind_param`); the identical bypass payload was retested and correctly rejected, with no other logic changed.

Testing covered DVWA's Low, Medium, High, and Impossible security levels throughout.

---

## Repository Structure

```
sqli-attack-project/
├── screenshots/         # Evidence screenshots, numbered folders (see mapping below)
├── reports/
│   ├── output1-6.txt                       # Raw SQLMap terminal output (detection → dump)
│   ├── ZAP by Checkmarx Scanning Report.pdf # Full OWASP ZAP scan report
│   └── SQL_Injection_Attack_Final_Report.pdf # Final consolidated report
└── code/
    ├── vulnerable-app/  # Original mini-app: raw string-concatenated query (exploitable)
    ├── fixed-app/       # Same app: parameterized query via mysqli_prepare (not exploitable)
    └── setup.sql        # Schema + seed data for both apps
```

**Note on screenshot folder numbering:** the numbered folders reflect the order screenshots were taken in, not a strict 1-to-1 mapping to a fixed day count. Folder `7` contains both the SQLMap and Burp Suite sessions (captured the same day); folder `8` contains the ZAP scan evidence; folder `9` contains the vulnerable-vs-fixed app demonstration.

`reports/output1.txt`–`output2.txt` = initial SQLMap detection · `output3.txt` = database enumeration (`--dbs`) · `output4.txt` = table enumeration (`--tables`) · `output5.txt` = column enumeration (`--columns`) · `output6.txt` = final credential dump (`--dump`).

---

## Tools Used

| Tool | Purpose |
|---|---|
| DVWA | Deliberately vulnerable lab target |
| SQLMap | Automated SQLi detection and exploitation |
| Burp Suite | Manual HTTP request interception and replay |
| OWASP ZAP | Automated whole-application vulnerability scanning |
| Custom PHP/MySQL mini-app | Original vulnerable-vs-fixed comparison, independent of DVWA |

---

## Key Findings

| Finding | Detail |
|---|---|
| Unsanitized input (CWE-89) | `id` parameter concatenated directly into the SQL query, no validation or parameterization |
| Authentication/data bypass | OR-based payloads return all rows regardless of the supplied ID |
| Full schema disclosure | All tables/columns enumerable via `information_schema` |
| Credential exposure | All 5 user password hashes extracted and cracked (unsalted MD5) |
| Weak/reused passwords | Two accounts share an identical password hash |
| Verbose error disclosure | Raw SQL errors returned directly to the client (HTTP 500 with DB error text) |
| Missing security headers | CSP, X-Content-Type-Options, anti-clickjacking headers absent (per ZAP) |
| Cookie hardening gaps | Session cookies missing `HttpOnly` and `SameSite` flags (per ZAP) |

**Fix demonstrated:** replacing string-concatenated queries with parameterized queries (prepared statements) fully closes the exploited vulnerability — see `code/vulnerable-app` vs `code/fixed-app`.

---

## Running the Vulnerable/Fixed Demo Locally

1. Import the schema: `mysql -u root -p < code/setup.sql`
2. Update DB credentials in `code/vulnerable-app/config.php` and `code/fixed-app/config.php`
3. Serve either app with PHP's built-in server, e.g.:
   ```
   php -S localhost:8000 -t code/vulnerable-app
   ```
4. Try the payload `' OR '1'='1' --` in the username field on both apps and compare the result.

---

## Full Report

The complete write-up — theory, full methodology, findings, and conclusion — is at [`reports/SQL_Injection_Attack_Final_Report.pdf`](reports/SQL_Injection_Attack_Final_Report.pdf).

---

## References

- OWASP Foundation — SQL Injection (owasp.org)
- OWASP Top 10 — Web Application Security Risks
- DVWA — Damn Vulnerable Web Application (github.com/digininja/DVWA)
- SQLMap documentation (sqlmap.org)
- PortSwigger Burp Suite documentation
- OWASP ZAP documentation (zaproxy.org)
