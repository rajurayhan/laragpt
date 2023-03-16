const API = {
    baseUrl: 'http://localhost:800/api/',
    // GET request
    get: async (url) => {
      const response = await fetch(url);
      return response.json();
    },
    
    // POST request
    post: async (url, data) => {
      const response = await fetch(url, {
        method: 'POST',
        body: JSON.stringify(data),
        headers: {
          'Content-Type': 'application/json'
        }
      });
      return response.json();
    },
    
    // PUT request
    put: async (url, data) => {
      const response = await fetch(url, {
        method: 'PUT',
        body: JSON.stringify(data),
        headers: {
          'Content-Type': 'application/json'
        }
      });
      return response.json();
    },
    
    // DELETE request
    delete: async (url) => {
      const response = await fetch(url, {
        method: 'DELETE',
      });
      return response.json();
    }
  }; 
  