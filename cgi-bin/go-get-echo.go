package main

import (
	"fmt"
	"net/http"
	"net/http/cgi"
)

func main() {
	cgi.Serve(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Cache-Control", "no-cache")
		w.Header().Set("Content-Type", "text/html")

		fmt.Fprintf(w, `<!DOCTYPE html>
<html><head><title>GET Request Echo</title>
</head><body><h1 align="center">Get Request Echo</h1>
<hr>
<b>Query String:</b> %s<br />`, r.URL.RawQuery)

		// Parse Query String
		// Go handles the splitting and decoding automatically
		queryValues := r.URL.Query()

		for key, values := range queryValues {
			// Values is a list (in case of ?a=1&a=2), we just take the first for simple echo
			fmt.Fprintf(w, "%s = %s<br/>\n", key, values[0])
		}

		fmt.Fprint(w, "</body></html>")
	}))
}