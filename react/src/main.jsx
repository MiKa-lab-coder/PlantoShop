import React from 'react';
import ReactDOM from 'react-dom/client';
import { createBrowserRouter, RouterProvider } from 'react-router-dom';

import App from './App.jsx';
import HomePage from './pages/HomePage.jsx';
import LoginPage from './pages/LoginPage.jsx';
import RegisterPage from './pages/RegisterPage.jsx';
import ContactPage from './pages/ContactPage.jsx';
import './index.css';

// On crée notre routeur ici
const router = createBrowserRouter([
    {
        path: '/', // La route racine
        element: <App />, // Le composant "coquille"
        // Les enfants qui seront affichés dans le <Outlet> de App
        children: [
            {
                path: '/', // Si l'URL est '/', affiche HomePage
                element: <HomePage />,
            },
            {
                path: '/login', // Si l'URL est '/login', affiche LoginPage
                element: <LoginPage />,
            },
            {
                path: '/register', // Si l'URL est '/register', affiche RegisterPage
                element: <RegisterPage/>,
            },
            {
                path: 'contact', // Si l'URL est '/contact', affiche ContactPage'
                element: <ContactPage/>,
            }
        ],
    },
]);

// On dit à React de rendre le ROUTEUR, et non plus App directement.
ReactDOM.createRoot(document.getElementById('root')).render(
    <React.StrictMode>
        <RouterProvider router={router} />
    </React.StrictMode>
);
    