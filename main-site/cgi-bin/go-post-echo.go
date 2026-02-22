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

		fmt.Fprint(w, `<!DOCTYPE html>
<html><head><title>POST Request Echo</title>
</head><body><h1 align="center">POST Request Echo</h1>
<hr>
<b>Message Body:</b><br />
<ul>`)

		// Parse Form Data (Reads body internally)
		r.ParseForm()

		// Iterate over POST values
		for key, values := range r.PostForm {
			fmt.Fprintf(w, "<li>%s = %s</li>\n", key, values[0])
		}

		fmt.Fprint(w, "</ul>\n</body></html>")
	}))
}