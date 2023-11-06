import ReactDOM from 'react-dom'
import { useState, useEffect } from 'react'

const App = () => {
  const [state, setState] = useState(null)

  const userInput = 'User input string'

  useEffect(() => {
    fetch('/wp-json/chatgpt/v1/message', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ message: userInput }),
    }).then((response) => response.json())
      // .then((data) => setState(data))
      .then((data) => {
        // Handle the response from PHP backend.
        // console.log(JSON.stringify(data));
      })
  }, [])

  return <p>App {state || 'Loading...'}</p>
}

ReactDOM.render(<App/>, document.getElementById('chatgpt-app'))
