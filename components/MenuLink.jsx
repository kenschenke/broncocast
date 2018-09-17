import React from 'react';
import PropTypes from 'prop-types';
import { Route, NavLink } from 'react-router-dom';

export class MenuLink extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <Route path={this.props.to} children={({match}) => (
                <li className={'nav-item' + (match ? ' active' : '')}>
                    <NavLink className={'nav-link'} to={this.props.to}>{this.props.label}</NavLink>
                </li>
            )}/>
        );
    }
}

MenuLink.propTypes = {
    to: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired
};
